# Elasticsearch faceted catalog (plan)

Working plan for replacing MySQL `LIKE` + Eloquent filters on the storefront
catalog with Elasticsearch. **Do not use Laravel Scout** — the catalog needs
faceted search (filters + aggregations + sort + pagination in one query), which
Scout’s “search → IDs → hydrate” model does not fit well.

Status: **Phase 3 done locally** (query layer behind `CATALOG_SEARCH_DRIVER`; cutover still Phase 4).

Related code today:

| Piece | Path |
| --- | --- |
| API catalog | `src/app/Http/Controllers/Api/CatalogController.php` |
| Catalog orchestration | `src/app/Services/CatalogService.php` |
| Filters on Product query | `src/app/Services/ProductService.php` |
| Filter catalog for UI | `src/app/Services/FilterService.php` |
| Path → filters | `src/app/Http/Requests/FilterRequest.php` |
| LIKE search | `src/app/Services/SearchService.php` + `Product::scopeSearch` |
| Filter contract | `src/app/Contracts/Filterable.php`, `AttributeFilterTrait` |
| Sync hooks (no Product observer) | `ProductCreated` / `ProductUpdated`, `UpdateAvailabilityJob` |
| Web storefront | Removed; unmatched paths go via `Route::fallback` → `front_redirect` |

---

## Goals

1. Storefront catalog listing (search + multi-filters + sort + page) served from ES.
2. Facet counts that respect the current selection (typical e-com: counts for
   other dimensions exclude the active filter of that same dimension).
3. Keep the **public API response shape** stable for the external Vue storefront
   (`products`, `filters`, `badges`, `currentFilters`, `sort`, `meta`, …).
4. MySQL remains source of truth (PDP, cart, admin, orders). ES is a **read model**.
5. Async index sync via Horizon/Redis queues; full reindex command for rebuilds.

Non-goals (for now):

- Scout / Meilisearch / Typesense.
- City-based listing filters (city today is SEO/path only; stock visibility is
  soft-delete via `UpdateAvailabilityJob`).
- Moving Filament admin search to ES.
- Changing canonical filter URL scheme (`Url` + path slugs).
- Facet **counts** in API v1 (full filter dictionary only, same as today).
- Perfect `promotion` Status sync into ES (deferred; see decisions).

---

## Decisions (Phase 0)

| # | Question | Decision |
| --- | --- | --- |
| 1 | Hosting | **Self-hosted Elasticsearch** (Sail locally; same on prod/nearby host). |
| 2 | Facet counts in v1 | **No** — listing + search + sort + min/max; `FilterService::getAll()` unchanged. Counts later if Vue needs them. |
| 3 | `promotion` / Sale sync | **Deferred** — revisit when indexing Status filters; `st-new` / `st-sale` can be document fields from product prices. |
| 4 | Legacy Blade catalog | **Remove first** (done) — ES only for API afterward. |

Still open before Phase 1:

- [x] Confirm search vs sort behaviour when `q` is present.
- [ ] Pick packages: official client ± `elastic-client` / `elastic-migrations`.

---

## Architecture

```text
MySQL (Product, attributes, stock, urls)
        │
        │  events / jobs (create, update, availability)
        ▼
  Indexer (document builder + bulk upsert / delete)
        │
        ▼
  Elasticsearch alias: catalog
        │
        ▼
  CatalogSearchService (query DSL: q + filters + aggs + sort + from/size)
        │
        ▼
  CatalogService / Api\CatalogController
        │  (hydrate Eloquent by IDs only if resource still needs relations)
        ▼
  Unchanged JSON for Vue
```

Decision: **`babenkoivan/elastic-client` + `babenkoivan/elastic-migrations`**
(official `elasticsearch/elasticsearch` under the hood). No Scout.
Optional later: `spatie/elasticsearch-query-builder` for fluent search DSL.

Feature flag (suggested): `CATALOG_SEARCH_DRIVER=mysql|elasticsearch` so we can
shadow-compare and cut over safely.

---

## Index document (draft)

One document per sellable `Product` (`_id` = product id). Soft-deleted /
unavailable products are **removed** from the index (same semantics as today:
not in listing).

Suggested fields (adjust during mapping design):

| Field | Type / notes |
| --- | --- |
| `id` | integer |
| `sku`, `slug`, `short_name` | keyword + text where needed |
| `color_txt` | text (RU analyzer) |
| `price`, `old_price` | float/scaled_float |
| `has_discount` | boolean (`price < old_price`) |
| `is_new` | boolean (align with Status `st-new` / `old_price = 0` rules) |
| `category_id`, `category_ids` | leaf + ancestor IDs (subtree filter like today) |
| `brand_id`, `season_id`, `collection_id` | keyword/integer |
| `status_slugs` | keyword (`st-sale`, `st-new`, …) — encode Status filter logic |
| `size_ids`, `color_ids`, `fabric_ids`, `heel_ids`, `style_ids`, `tag_ids` | keyword/integer arrays |
| `*_slugs` | optional denormalized slugs for debug / direct filter |
| `search_text` | copy_to / combined text: sku, brand, category, tags, color_txt, … |
| `rating`, `newness_rating`, `season_rating`, `sale_rating` | float/integer for sort |
| `created_at` | date (search currently forces `created_at desc` when q present — decide if we keep) |
| `media` / listing payload | optional denormalization later; **v1 hydrate from MySQL by IDs** |

Analyzers: Russian (+ maybe edge-ngram for autocomplete later). Prefer explicit
mapping; avoid relying on dynamic mapping in production.

Alias strategy: app always queries alias `catalog`; physical indices
`catalog_vN`; zero-downtime reindex = new index → reindex → atomic alias swap.

---

## Query behaviour (parity with today)

### Filters

Current dimensions (`FilterService::FILTERS_MODELS` + static):

- categories, statuses, fabrics, collections, sizes, colors, heels, seasons,
  styles, tags, brands
- Price: `price-from-{n}` / `price-to-{n}`
- Top pins: query `top=` (exclude from page + prepend) — can stay in PHP after ES

Within one dimension: **OR** (`terms` / `whereIn`). Across dimensions: **AND**.

Category: last path segment wins; expand to subtree IDs (same as
`Category::getChildrenCategoriesIdsList`) → `terms` on `category_ids`.

Status:

- `st-new` / `st-sale` → document fields from product prices.
- `promotion` → **deferred** (see decisions); keep MySQL path or skip until designed.

### Search

Replace `SearchService` LIKE chains with `multi_match` / `bool` on `search_text`
(+ sku/id exact boost). Numeric single-token search for id/sku can stay as a
`term`/`ids` boost.

Decide explicitly whether search overrides sort (today `scopeSearch` forces
`created_at desc`). **Decision (ES):** relevance (`_score desc`, then `id`);
ignore UI `ProductSort` while `search` is non-empty. MySQL path unchanged.

### Sort

Map `ProductSort` to ES `sort`:

- `newness` → `newness_rating` desc, `id` desc
- `price-up` / `price-down` → `price`, `id`
- `rating` → `rating` | `season_rating` | `sale_rating` from
  `ProductRatingColumn::fromFilters`, then `id` desc

### Pagination

API: length-aware (`from`/`size` + `track_total_hits` or `hits.total`). Keep
`per_page` clamp 12–100. Cursor/session pagination for Blade catalog has been
**removed**.

### Facets / `filters` payload

v1: **Filter dictionary only** from MySQL / `FilterService::getAll()` (or cache),
same as today — no ES aggregations for counts.

### Min/max price

Today: clone query, strip price filters, compute min/max. In ES: `min`/`max`
aggs on `price` with the same “other filters + search, without price range”
context. Attach to paginator as now (`minPrice` / `maxPrice`).

### Response assembly

v1 path:

1. ES returns ordered product IDs (+ total, aggs, min/max).
2. Load `Product` by IDs preserving ES order (`whereIn` + sort by field).
3. Existing eager load / `CatalogProductResource` / badges / meta unchanged.

Later: denormalize listing fields into ES to skip hydrate (only if latency or
load requires it).

---

## Sync strategy

### Document builder

Single class, e.g. `App\Services\Elasticsearch\CatalogDocumentBuilder`, that
loads a Product with needed relations and builds the array above. Used by both
incremental jobs and full reindex.

### Triggers (incremental)

| Source | Action |
| --- | --- |
| `ProductCreated` / `ProductUpdated` | Upsert (or delete if soft-deleted / not listable) |
| `UpdateAvailabilityJob` side effects | Upsert/delete after stock → soft-delete/restore |
| Attribute / brand / category / … rename or slug change | Reindex affected products (or batch by attribute id) |
| Promotion / sale product set changes | **Deferred** with Status `promotion` |
| Hard delete | Delete from index |

Use queued jobs (`ShouldQueue`), idempotent upsert by product id. Debounce /
`unique` jobs if noisy updates are a problem.

### Full reindex

Artisan command, e.g. `catalog:elasticsearch-reindex`:

1. Create `catalog_vN` with current mapping migration.
2. Chunk Product query (only listable) → bulk index.
3. Swap alias `catalog` → new index.
4. Drop old index when safe.

### Local / Sail

Add Elasticsearch service to `src/docker-compose.yml`. Document env in
`.env.example` (`ELASTICSEARCH_HOST`, …). Never commit secrets.

---

## Implementation phases

### Phase 0 — Decisions / prep

- [x] Hosting: self-hosted Elasticsearch.
- [x] Facet counts: not in v1.
- [x] `promotion` sync: deferred.
- [x] Remove legacy Blade storefront (catalog/index/product) + cursor pagination; web unmatched → fallback.
- [x] Packages: `babenkoivan/elastic-client` + `babenkoivan/elastic-migrations` (Spatie QB optional in Phase 3).
- [x] Confirm search vs sort behaviour when `q` is present.

### Phase 1 — Infrastructure

- [x] Sail/compose ES 9.1.3 service + env + healthcheck.
- [x] Install `babenkoivan/elastic-client` + `babenkoivan/elastic-migrations` (via Sail).
- [x] Publish `config/elastic.client.php` + `config/elastic.migrations.php`; DB table `elastic_migrations`.
- [x] First elastic migration: `catalog_v1` mapping + alias `catalog`.
- [x] Feature flag env `CATALOG_SEARCH_DRIVER` (default `mysql`) + `config/catalog.php`.
- [x] Scaffold `CatalogDocumentBuilder` (+ unit tests).

### Phase 2 — Indexing

- [x] `CatalogIndexer` (bulk upsert/delete via alias).
- [x] Upsert job (delete-from-index when product missing/trashed).
- [x] Wire `ProductCreated` / `ProductUpdated` + availability job.
- [x] `catalog:elasticsearch-reindex` command.
- [ ] Feature/integration tests that need live ES — only locally/Sail, not CI.

### Phase 3 — Query layer

- [x] `CatalogSearchService` (or similar): build DSL from current filters +
      search + sort + page.
- [x] Map `FilterRequest` filter groups → ES filters (reuse same filter models /
      slugs; do not change URL parsing).
- [x] Aggregations for min/max price (not facet counts in v1).
- [x] Adapter behind `CatalogService` when driver = elasticsearch.

### Phase 4 — Cutover

- [ ] Shadow mode: run MySQL + ES, log mismatches (ids/order/total) for sample
      traffic or artisan compare command.
- [ ] Enable ES for API catalog in staging.
- [ ] Production cutover behind flag; keep MySQL path for rollback.
- [ ] Retire or narrow `SearchService` LIKE usage for catalog only after
      confidence.

### Phase 5 — Hardening (later)

- [ ] Zero-downtime mapping changes (reindex + alias).
- [ ] Monitoring: index lag, failed jobs, ES cluster health → Sentry/logs.
- [ ] Autocomplete / suggest index if needed.
- [ ] Optional listing denormalization to drop MySQL hydrate.
- [ ] Facet counts via aggregations if Vue needs them.
- [ ] Design `promotion` / Sale → index sync (event + reconcile).
- [ ] Consider removing unused `laravie/serialize-queries` (was Blade cursor cache).

---

## Suggested package layout (src)

```text
app/Services/Elasticsearch/
  CatalogDocumentBuilder.php
  CatalogIndexer.php
  CatalogSearchService.php
app/Jobs/Elasticsearch/
  UpsertCatalogProductJob.php
app/Console/Commands/
  CatalogElasticsearchReindexCommand.php
database/elastic-migrations/   # babenkoivan elastic-migrations path
```

Keep `CatalogService` as the façade used by `Api\CatalogController`; swap the
backend behind it so controllers stay thin.

---

## Testing notes

- Unit-test document builder field encoding (status/sale/new, category_ids) — no DB/ES.
- Unit-test DSL builder: AND across dimensions, OR within, price range,
  category subtree, sort maps.
- Live ES checks (migrate/reindex/API) — manual or Sail-only; **not CI** (no ES there).
- Keep MySQL catalog path tests green under `CATALOG_SEARCH_DRIVER=mysql`.

Run via Sail: `cd src && ./vendor/bin/sail artisan test --compact …`.

---

## Progress log

| Date | Note |
| --- | --- |
| 2026-07-25 | Plan created. Decision: faceted catalog → ES without Scout. |
| 2026-07-25 | Decisions: self_es; no facet counts in v1; promotion sync deferred; remove Blade catalog first. |
| 2026-07-25 | Removed Blade catalog: `Shop\CatalogController`, catalog/filter views, cursor pagination (`CatalogCursorPaginator`, `getProducts`/`getNextProducts`). Sitemap attribute URLs use `front_route`. |
| 2026-07-25 | Removed web `catalog/{path?}` / `shop` route entirely; catalog URLs rely on `Route::fallback` → `front_redirect`. |
| 2026-07-26 | Chose babenkoivan (`elastic-client` + `elastic-migrations`). Sail ES 9.1.3 up; client smoke OK; `elastic_migrations` table migrated. |
| 2026-07-26 | Elastic migrations path → `database/elastic-migrations`. Created `catalog_v1` + alias `catalog`; `CatalogDocumentBuilder` + tests. |
| 2026-07-26 | Phase 2: `CatalogIndexer`, `UpsertCatalogProductJob` (also deletes when missing/trashed), `SyncCatalogProduct`, availability → catalog jobs, `catalog:elasticsearch-reindex`. |
| 2026-07-26 | Dedicated Horizon queue `elasticsearch` + production `supervisor-elasticsearch` (`maxProcesses: 1`). |
| 2026-07-26 | Dropped unused `DeleteCatalogProductJob` (upsert covers index removal). |
| 2026-07-26 | Phase 3: `CatalogSearchService` + `CatalogService` driver switch; promotion → MySQL fallback; unit tests for DSL. |
| 2026-07-26 | ES search sort: `_score` (best match), not MySQL `created_at` parity. |
