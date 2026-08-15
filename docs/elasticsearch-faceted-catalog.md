# Elasticsearch faceted catalog (plan)

Working plan for storefront catalog search/filters via Elasticsearch.
**No Laravel Scout** — need filters + sort + pagination (+ later facet aggs)
in one query; Scout’s “search → IDs → hydrate” model does not fit well.

Status: **Catalog listing is ES-only on `/api/v2/catalog`**. MySQL catalog listing
(`/api/v1/catalog`) has been removed. PDP/cart/auth remain under `/api/v1`.
Physical ES index name `catalog_v1` is unrelated to API versioning.

---

## Goals

1. Catalog listing (search + multi-filters + sort + page + facets) from ES via `/api/v2/catalog`.
2. Facet counts (other dimensions ignore active filter of same dimension).
3. MySQL = source of truth (PDP, cart, admin, orders). ES = **read model**.
4. Async sync via Horizon; full reindex command for rebuilds.

Non-goals (for now):

- Scout / Meilisearch / Typesense.
- City-based listing filters (city is SEO/path only; stock → soft-delete).
- Filament admin search on ES.
- `promotion` Status in ES (deferred; ignored if passed).
- Full product denorm in ES (listing hydrate from MySQL; optional later).
- Indexing `description` (noise for catalog search).
- MySQL catalog listing / Filterable SQL filters (removed).

---

## Decisions

| # | Topic | Decision |
| --- | --- | --- |
| 1 | Hosting | Self-hosted ES (Sail locally; same idea on prod). |
| 2 | Packages | `babenkoivan/elastic-client` + `elastic-migrations`. No Scout. |
| 3 | Facet counts | On `/api/v2/catalog` only (disjunctive ES aggs + sparse MySQL hydrate). |
| 4 | `promotion` | Ignored on ES path (not indexed). |
| 5 | Blade storefront | Removed; web unmatched → `front_redirect`. |
| 6 | Catalog API | `/api/v2/catalog` only; `/api/v1/catalog` removed. |
| 7 | Search + sort | With `search`: `_score desc`, `id desc` (ignore UI sort). |
| 8 | Availability sync | Bulk `CatalogIndexer::syncProductIds` at end of job; no `pending_es_sync`. |
| 9 | Point updates | Unique `UpsertCatalogProductJob` (`tries=3`, `backoff=[10,30]`). |
| 10 | Attribute shape | ES `object` `{id, name}` (not full `{id,name,slug}`; not ES `nested`). Filters on `*.id`, search on `*.name`. |
| 11 | Full product in ES | Not now. Optional later: denorm only listing fields for v2. Attribute `slug` only if serving facets/cards from `_source`. |
| 12 | Top pin filter (`?top=`) | **Removed** — no longer used by frontends. |
| 13 | v2 response | Minimal independent contract: `products`, `banners`, `category`, `facets`, `sort`, `meta`. |
| 14 | Facet metadata | ES returns bucket keys/counts; MySQL hydrates only metadata for positive buckets. |
| 15 | ES query builder | Keep current explicit arrays + `elastic-adapter`; do not add `spatie/elasticsearch-query-builder` for now. |
| 16 | Facet definitions | `CatalogFacetName` lists facets; models own ES field + hydrate metadata. |

Resolved:

- [x] Storefront catalog is ES-only on `/api/v2/catalog`.
- [x] Facets expose `count` / `selected`; zero-count options are omitted.
- [x] Current category is minimal (`id`, `name`, `slug`, `path`) with a
      recursive `parent_category`.
- [x] MySQL listing path and SQL Filterable filters removed.

---

## Catalog API versioning

| Surface | Audience | Backend |
| --- | --- | --- |
| **v2** `GET /api/v2/catalog/…` | Storefront Vue | **Elasticsearch** |
| **v1** (non-catalog) | PDP, cart, auth, … | MySQL as before |

Routing (`RouteServiceProvider`):

- Allowlist `v1` + `v2` (`routes/api.php`, `routes/api.v2.php`).
- Fallback: **426** only for unknown `v*`; **404** inside supported versions.
- Names: `api.*` vs `api.v2.*`.

Shared: alias `catalog`, indexer, upsert jobs, reindex. Divergent: HTTP resources / filter payload.

---

## Architecture

```text
MySQL (Product, attributes, stock, urls)
        │  ProductCreated/Updated → UpsertCatalogProductJob
        │  UpdateAvailabilityJob → CatalogIndexer::syncProductIds
        ▼
  CatalogDocumentBuilder + CatalogIndexer
        ▼
  Elasticsearch alias: catalog  (physical catalog_v1)
        ▼
  CatalogSearchService
        ▼
  Api\CatalogController::index (/api/v2/catalog)
  → hydrate products + sparse facets + CategoryResource
```
      → hydrate product cards from MySQL
      → hydrate positive facet bucket metadata from MySQL
      → minimal independent v2 JSON
```

Env: `ELASTIC_HOST`, `CATALOG_ELASTICSEARCH_ALIAS` / `INDEX`, …
Alias strategy: query `catalog`; mapping changes → new `catalog_vN` + reindex + alias swap (when needed in prod).

There is **no** `CATALOG_SEARCH_DRIVER`: v1 never uses ES; v2 always does.

---

## Index document (current)

One doc per sellable product (`_id` = id). Soft-deleted / missing → **deleted**
from index.

| Field | Notes |
| --- | --- |
| `id`, `sku` (+ `sku.text`), `slug` | sku keyword for exact/wildcard; `sku.text` = `catalog_sku` analyzer |
| `short_name`, `color_txt` | text, `catalog_russian` |
| `brand`, `categories`, `sizes`, `colors`, `tags` | object `{id, name}` |
| `season_id`, `collection_id` | integer |
| `fabric_ids`, `heel_ids`, `style_ids` | integer arrays (filter-only; no names yet) |
| `price`, `old_price` | scaled_float |
| `has_discount`, `is_new`, `status_slugs` | from prices (`st-new` / `st-sale`) |
| `rating`, `newness_rating`, `season_rating`, `sale_rating` | sort |
| `created_at` | date |

**Not indexed:** `description`, attribute `slug`, images / full listing payload.

### Analysis

- `catalog_russian`: `ё→е` char_filter + lowercase + `_russian_` stop + **russian_stemmer**.
- `catalog_sku`: `ё→е` + keyword tokenizer + `word_delimiter_graph` (SKU only).

Migration: `database/elastic-migrations/2026_07_26_005107_create_catalog_v1_index.php`
(single create; not yet shipped to prod — edit in place OK until first deploy).

---

## Query behaviour

### Filters

Within dimension **OR**, across **AND**. Map:

- `brand.id`, `categories.id`, `sizes.id`, `colors.id`, `tags.id`
- `fabric_ids`, `heel_ids`, `style_ids`, `season_id`, `collection_id`
- `status_slugs` for `st-new` / `st-sale`
- Category: last path segment → `term` on `categories.id` (chain stored in doc)
- Top-level query contains text search only.
- All selected filters, including price, are applied to hits through
  `post_filter`.
- Every facet aggregation applies all filter groups except its own group.
- Price is included when counting other facets; min/max price apply all groups
  except the selected price range.
- `promotion` → ignored (not in ES)

### Search

- Noise words (query-time): `размер` / `цвет` (+ forms).
- Text: per-word `multi_match` on boosted fields, `fuzziness: AUTO`,
  `minimum_should_match` = n (if n≤2) else n−1.
- Boosts: `sku.text^12`, `brand.name^7`, `short_name^6`, `categories.name^5`,
  then `color_txt`, `sizes.name`, `colors.name`, `tags.name`.
- Numeric single token: `id` term / `sku` wildcard / `sizes.name` / `short_name`.

### Sort / pagination / min-max

- No search: `ProductSort` → rating/price/newness fields + `id`.
- With search: `_score`, `id`.
- `from`/`size`, `per_page` 12–100, `track_total_hits`.
- Aggs: filtered `min_price` / `max_price` plus disjunctive terms facets.

### Response

- ES IDs → Eloquent product hydrate; ES buckets → sparse MySQL facet metadata
  hydrate. Top-level keys: `products`, `banners`, `category`, `facets`, `sort`,
  `meta`.
- Facet items contain only client fields (`id`, `slug`, display name,
  `count`, `selected`, and dimension-specific UI values). PHP model class names,
  SEO columns and timestamps are not exposed.
- Categories are returned as a sparse tree with `children`; current `category`
  separately contains the recursive parent chain.

---

## Sync

| Source | Action |
| --- | --- |
| `ProductCreated` / `ProductUpdated` | `UpsertCatalogProductJob` (unique) |
| `UpdateAvailabilityJob` | `CatalogIndexer::syncProductIds` (chunked bulk) |
| Attribute rename affecting many products | Reindex affected / full reindex |
| `promotion` set changes | Deferred |
| Missing / trashed | Delete from index |

Command: `catalog:elasticsearch-reindex` (`--fresh`, `--chunk`).
Horizon queue `elasticsearch` (prod supervisor `maxProcesses: 1`).

`pending_es_sync`: **not used** unless bulk-in-availability or multi-writer gaps appear.

---

## Code map

| Piece | Path |
| --- | --- |
| Catalog API | `Http/Controllers/Api/CatalogController.php::index`, `routes/api.v2.php` |
| PDP (still v1) | `Http/Controllers/Api/CatalogController.php::show` |
| Hydrate / paginate | `Services/CatalogService.php` |
| ES document / index / search | `Services/Elasticsearch/*` |
| Filterable ES clauses | `Contracts/Filterable.php`, `Traits/Filterable` (as `FilterableTrait`) |
| Facet list / names | `Enums/Catalog/CatalogFacetName.php` (`model()` → Filterable) |
| Facet hydrate | `Services/Elasticsearch/CatalogFacetService.php` |
| Category API resource | `Http/Resources/Product/CategoryResource.php` |
| Upsert job | `Jobs/Elasticsearch/UpsertCatalogProductJob.php` |
| Availability → ES | `Jobs/AvailableSizes/UpdateAvailabilityJob.php` |
| Reindex | `Console/Commands/CatalogElasticsearchReindexCommand.php` |
| Elastic migration | `database/elastic-migrations/` |
| Config | `config/catalog.php` |
| Search token helpers | `Services/SearchService.php` (used by ES search) |

vs `feature/elasticsearch-catalog-v2` (reference only, no wholesale merge):

- They: PHP `ProductIndex::create`, index `products`, `{id,name,slug}`, images,
  all attrs as objects, `word_delimiter` on all text, **no** stemmer, facet aggs,
  `pending_es_sync`.
- We: elastic-migrations + alias, stemmer, delimiter on SKU only, thinner doc,
  hydrate listing + sparse facet metadata, disjunctive facet aggs done.

---

## Query builder library evaluation

`spatie/elasticsearch-query-builder` 3.10 is active and supports the primitives
used here: bool/term/range/multi-match queries, `post_filter`, filtered
aggregations, terms aggregations, sorting and pagination.

It is not adopted because it does not remove the catalog-specific complexity:

- excluding a different filter group for every facet still requires our own
  grouping and orchestration;
- sparse metadata hydration and category-tree building remain application code;
- the current `babenkoivan/elastic-adapter` already supplies `SearchParameters`,
  execution and normalized result objects;
- Spatie's builder executes through the official client directly and returns
  its raw response, so adopting it would create two query/result abstractions or
  require replacing the working adapter path;
- the installed client is Elasticsearch PHP v9, while Spatie's current
  development matrix declares/tests v8 only (the package does not constrain the
  client as a runtime dependency).

The fluent syntax would improve readability for simple queries, but the current
request is already unit-tested as an array payload. The migration cost and extra
dependency are larger than the benefit. Reconsider only if several unrelated ES
queries appear and they can share one builder abstraction.

## Facet definition organization

Current choice: ES field names and filter/facet metadata live on `Filterable`
models (`elasticField()`, `elasticFilterClauses()`, `elasticFacet*()`).
`CatalogFacetName` is the ordered list of catalog facets and maps each case to
its model via `model()`.

Special cases override `elasticFilterClauses()` / facet metadata on the model:

- `Price` → range on `price`
- `Status` → terms on `status_slugs` (ignores `promotion`)
- `Category` → last path segment on `categories.id`

---

## Phases

### Done

- [x] Phase 0 — decisions, Blade removal, packages, search-sort behaviour.
- [x] Phase 1 — Sail ES, migrations, `catalog_v1` + alias, document builder.
- [x] Phase 2 — indexer, upsert job, event wiring, availability bulk, reindex command, Horizon queue.
- [x] Phase 3 — `CatalogSearchService` for **API v2** (filters/search/sort/min-max).
- [x] Phase 4 — independent minimal v2 response, facets, SEO category resource.
- [x] Phase 5 — storefront on `/api/v2/catalog`; MySQL listing + SQL Filterable removed.

### Phase 6 — Later

- [ ] Zero-downtime mapping (`catalog_vN` + alias swap) when prod is live.
- [ ] Monitoring: index lag, failed jobs, cluster health.
- [ ] Autocomplete / suggest if needed.
- [ ] Optional listing denorm (drop MySQL hydrate on v2).
- [ ] `promotion` / Sale → index design.
- [ ] Attribute rename → targeted reindex.
- [ ] Live-ES integration tests — Sail only, not CI.

---

## Testing

- Unit: document builder, search DSL (no live ES).
- Feature: API version routing, minimal v2 contract, category parent chain.
- Live ES (migrate / reindex / API v2 combinations): manual via Sail; **not CI**.

```bash
cd src && ./vendor/bin/sail artisan elastic:migrate
cd src && ./vendor/bin/sail artisan catalog:elasticsearch-reindex --fresh
cd src && ./vendor/bin/sail artisan test --compact tests/Unit/Elastic tests/Feature/Api
```

---

## Progress log (recent)

| Date | Note |
| --- | --- |
| 2026-07-25 | Plan; Blade storefront removed. |
| 2026-07-26 | Phases 1–3: ES infra, indexer, `CatalogSearchService`, driver switch. |
| 2026-07-26 | Phase 4 start: v1+v2 routing, v2 skeleton, availability bulk sync. |
| 2026-07-26 | Mapping: `{id,name}` objects, `ё→е`+stemmer, sku delimiter; fuzzy MSM search; no `description`. |
| 2026-07-27 | Plan refreshed to match current code; dropped obsolete drafts. |
| 2026-07-27 | ES only on API v2; removed `CATALOG_SEARCH_DRIVER` / v1 ES path. |
| 2026-07-28 | v2 response ⊇ v1 keys; `filters` may diverge later (facets). |
| 2026-07-30 | Removed catalog Top pin filter (`?top=`) from v1/v2. |
| 2026-08-04 | Added disjunctive facets, sparse metadata hydration and positive-count-only output. |
| 2026-08-04 | Replaced v1-shaped response with minimal v2 contract and recursive category resource. |
| 2026-08-04 | Fixed self-filter and price semantics; statuses are OR within their dimension. |
| 2026-08-15 | Removed `/api/v1/catalog` MySQL listing; Filterable is ES-only. |
| 2026-08-15 | Moved ES catalog into `Api\CatalogController`; dropped `RedirectOldProductUrls`. |
