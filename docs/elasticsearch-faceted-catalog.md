# Elasticsearch faceted catalog (plan)

Working plan for replacing MySQL `LIKE` + Eloquent filters on the storefront
catalog with Elasticsearch. **Do not use Laravel Scout** — the catalog needs
faceted search (filters + aggregations + sort + pagination in one query), which
Scout’s “search → IDs → hydrate” model does not fit well.

Status: **planning** (no ES in the repo yet).

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

Decision: **direct official `elasticsearch/elasticsearch` client**, optional
helpers:

- `babenkoivan/elastic-client` — Laravel binding for the client
- `babenkoivan/elastic-migrations` — index/mapping versioning (`elastic:migrate`)

No Scout drivers.

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

Status: do **not** reimplement sale/new SQL in ES ad hoc — encode into document
fields (`has_discount`, `is_new`, `status_slugs`, or promotion product IDs
refreshed when sales change).

### Search

Replace `SearchService` LIKE chains with `multi_match` / `bool` on `search_text`
(+ sku/id exact boost). Numeric single-token search for id/sku can stay as a
`term`/`ids` boost.

Decide explicitly whether search overrides sort (today `scopeSearch` forces
`created_at desc`). Prefer: relevance sort when `q` present, else requested
`ProductSort`.

### Sort

Map `ProductSort` to ES `sort`:

- `newness` → `newness_rating` desc, `id` desc
- `price-up` / `price-down` → `price`, `id`
- `rating` → `rating` | `season_rating` | `sale_rating` from
  `ProductRatingColumn::fromFilters`, then `id` desc

### Pagination

API: length-aware (`from`/`size` + `track_total_hits` or `hits.total`). Keep
`per_page` clamp 12–100. Cursor/session pagination for legacy Blade can remain
on MySQL until retired.

### Facets / `filters` payload

Two layers:

1. **Filter dictionary** (labels, slugs, order) — still from MySQL /
   `FilterService::getAll()` (or cache), same as today.
2. **Availability / counts** — from ES aggregations on the current query
   (optional enhancement). v1 can keep returning the full filter list without
   counts if Vue does not need counts yet; confirm with frontend before building
   dynamic facet UX.

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
| Promotion / sale product set changes | Refresh `status_slugs` / promotion flags for affected IDs |
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

Add Elasticsearch (or OpenSearch if preferred later) service to
`src/docker-compose.yml`. Document env in `.env.example`
(`ELASTICSEARCH_HOST`, …). Never commit secrets.

---

## Implementation phases

### Phase 0 — Decisions (short)

- [ ] Confirm ES vs OpenSearch for hosting.
- [ ] Confirm whether Vue needs live facet counts in v1.
- [ ] Confirm search vs sort behaviour when `q` is present.
- [ ] Pick packages: official client ± `elastic-client` / `elastic-migrations`.

### Phase 1 — Infrastructure

- [ ] Sail/compose ES service + env + healthcheck.
- [ ] Install client packages (via Sail composer).
- [ ] Config `config/elasticsearch.php` (or package config).
- [ ] First elastic migration: `catalog` mapping + alias.
- [ ] Feature flag `CATALOG_SEARCH_DRIVER`.

### Phase 2 — Indexing

- [ ] `CatalogDocumentBuilder`.
- [ ] Upsert/delete job(s).
- [ ] Wire `ProductCreated` / `ProductUpdated` + availability job.
- [ ] `catalog:elasticsearch-reindex` command.
- [ ] Feature tests with ES test container or HTTP fake for unit pieces;
      one integration test against Sail ES if feasible.

### Phase 3 — Query layer

- [ ] `CatalogSearchService` (or similar): build DSL from current filters +
      search + sort + page.
- [ ] Map `FilterRequest` filter groups → ES filters (reuse same filter models /
      slugs; do not change URL parsing).
- [ ] Aggregations for min/max price; optional facet counts.
- [ ] Adapter behind `CatalogService` when driver = elasticsearch.

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
- [ ] Invalidate/replace day-long `filters` cache strategy if facets become
      dynamic.

---

## Suggested package layout (src)

```text
app/Services/Elasticsearch/
  CatalogDocumentBuilder.php
  CatalogIndexer.php
  CatalogSearchService.php
app/Jobs/Elasticsearch/
  UpsertCatalogProductJob.php
  DeleteCatalogProductJob.php
app/Console/Commands/
  CatalogElasticsearchReindexCommand.php
database/elastic/   # or package default path for elastic-migrations
```

Keep `CatalogService` as the façade used by `Api\CatalogController`; swap the
backend behind it so controllers stay thin.

---

## Testing notes

- Unit-test document builder field encoding (status/sale/new, category_ids).
- Unit-test DSL builder: AND across dimensions, OR within, price range,
  category subtree, sort maps.
- Feature-test API catalog with flag = elasticsearch (Sail ES) for one happy
  path: search + brand + size + sort + pagination + min/max.
- Keep existing MySQL catalog tests green under `CATALOG_SEARCH_DRIVER=mysql`.

Run via Sail: `cd src && ./vendor/bin/sail artisan test --compact …`.

---

## Open questions

1. Hosting: managed Elasticsearch, self-hosted, or OpenSearch-compatible API?
2. Do we need facet **counts** in the API in v1, or only faster filtered listing?
3. Should promotion (`Status` / sales) products be a periodic reindex of a set,
   or event-driven from sale changes?
4. Legacy Blade catalog / cursor pagination — migrate to ES or leave on MySQL
   until removed?

---

## Progress log

| Date | Note |
| --- | --- |
| 2026-07-25 | Plan created. Decision: faceted catalog → ES without Scout. |
