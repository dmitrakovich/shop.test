# Elasticsearch faceted catalog (plan)

Working plan for storefront catalog search/filters via Elasticsearch.
**No Laravel Scout** — need filters + sort + pagination (+ later facet aggs)
in one query; Scout’s “search → IDs → hydrate” model does not fit well.

Status: **Phases 1–4 backend done**. Prod Vue stays on API v1; the next step is
connecting the test frontend to API v2 and validating the contract.

---

## Goals

1. Catalog listing (search + multi-filters + sort + page) from ES.
2. Facet counts (other dimensions ignore active filter of same dimension) —
   primarily **API v2**; not on v1 for now.
3. **API v1** JSON stays stable for production Vue.
4. **API v2** may change freely for the test / next frontend.
5. MySQL = source of truth (PDP, cart, admin, orders). ES = **read model**.
6. Async sync via Horizon; full reindex command for rebuilds.

Non-goals (for now):

- Scout / Meilisearch / Typesense.
- City-based listing filters (city is SEO/path only; stock → soft-delete).
- Filament admin search on ES.
- Changing filter URL scheme on **API v1**.
- Facet counts in API v1.
- `promotion` Status in ES (deferred).
- Full product denorm in ES (listing hydrate from MySQL; optional later).
- Indexing `description` (noise for catalog search).

---

## Decisions

| # | Topic | Decision |
| --- | --- | --- |
| 1 | Hosting | Self-hosted ES (Sail locally; same idea on prod). |
| 2 | Packages | `babenkoivan/elastic-client` + `elastic-migrations`. No Scout. |
| 3 | Facet counts in v1 | No — dictionary from `FilterService::getAll()`. |
| 4 | `promotion` | Ignored on v2 ES path (not indexed); v1 still applies via MySQL Sale. |
| 5 | Blade storefront | Removed; web unmatched → `front_redirect`. |
| 6 | API v2 | Yes — `/api/v2/catalog`; v1 until cutover. |
| 7 | Search + sort | With `search`: `_score desc`, `id desc` (ignore UI sort). |
| 8 | Availability sync | Bulk `CatalogIndexer::syncProductIds` at end of job; no `pending_es_sync`. |
| 9 | Point updates | Unique `UpsertCatalogProductJob` (`tries=3`, `backoff=[10,30]`). |
| 10 | Attribute shape | ES `object` `{id, name}` (not full `{id,name,slug}`; not ES `nested`). Filters on `*.id`, search on `*.name`. |
| 11 | Full product in ES | Not now. Optional later: denorm only listing fields for v2. Attribute `slug` only if serving facets/cards from `_source`. |
| 12 | Top pin filter (`?top=`) | **Removed** — no longer used by frontends. |
| 13 | v2 response | Minimal independent contract: `products`, `banners`, `category`, `facets`, `sort`, `meta`. |
| 14 | Facet metadata | ES returns bucket keys/counts; MySQL hydrates only metadata for positive buckets. |
| 15 | ES query builder | Keep current explicit arrays + `elastic-adapter`; do not add `spatie/elasticsearch-query-builder` for now. |
| 16 | Facet definitions | Keep the central ES-layer registry; do not attach ES metadata to Eloquent models. |

Resolved:

- [x] API v2 is independent from v1 and exposes `facets` with `count` /
      `selected`; zero-count options are omitted.
- [x] Current category is minimal (`id`, `name`, `slug`, `path`) with a
      recursive `parent_category`.

Still open:

- [ ] Validate the v2 payload with the test frontend and adjust only concrete
      frontend needs.

---

## Catalog API versioning

| Surface | Audience | Contract | Backend |
| --- | --- | --- | --- |
| **v1** `GET /api/v1/catalog/…` | Prod Vue | Stable | **MySQL only** |
| **v2** `GET /api/v2/catalog/…` | Test / next Vue | Free to change | **Elasticsearch only** |

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
  v1: CatalogService (MySQL) → stable JSON
  v2: Api\V2\CatalogController + CatalogSearchService
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
- `promotion` → ignored on v2 (not in ES); v1 handles via MySQL Sale path as today

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

- **v1:** MySQL listing → existing resources / badges / meta.
- **v2:** ES IDs → Eloquent product hydrate; ES buckets → sparse MySQL facet
  metadata hydrate. Top-level keys: `products`, `banners`, `category`, `facets`,
  `sort`, `meta`.
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
| v1 catalog API | `Http/Controllers/Api/CatalogController.php` |
| v2 catalog API | `Http/Controllers/Api/V2/CatalogController.php`, `routes/api.v2.php` |
| Orchestration (v1) | `Services/CatalogService.php` |
| ES document / index / search | `Services/Elasticsearch/*` |
| Facet registry / metadata | `Enums/Catalog/CatalogFacetName.php`, `Services/Elasticsearch/CatalogFacetConfig.php`, `CatalogFacetDefinition.php`, `CatalogFacetService.php` |
| Category API resource | `Http/Resources/Product/CategoryResource.php` |
| Upsert job | `Jobs/Elasticsearch/UpsertCatalogProductJob.php` |
| Availability → ES | `Jobs/AvailableSizes/UpdateAvailabilityJob.php` |
| Reindex | `Console/Commands/CatalogElasticsearchReindexCommand.php` |
| Elastic migration | `database/elastic-migrations/` |
| Config | `config/catalog.php` |
| MySQL LIKE (fallback) | `Services/SearchService.php`, `Product::scopeSearch` |

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

Current choice: keep `CatalogFacetDefinition` as the central registry inside the
Elasticsearch layer.

Alternatives considered:

1. **One class per facet** — strongest isolation and typing, but creates
   boilerplate for the eight facets that use identical behavior. Consider when
   bespoke facets become common.
2. **Trait on filter models** — concise, but spreads ES field names and API
   projection concerns across domain models. Not recommended.
3. **Interface on filter models** — enables polymorphism, but overlaps the
   existing MySQL-oriented `Filterable` contract and still handles category,
   status and price awkwardly. Not recommended.
4. **Central typed registry** — implemented with the `CatalogFacetName` enum and
   readonly `CatalogFacetConfig` DTO. Extract strategies only for category,
   status or price if their branches grow.

---

## Phases

### Done

- [x] Phase 0 — decisions, Blade removal, packages, search-sort behaviour.
- [x] Phase 1 — Sail ES, migrations, `catalog_v1` + alias, document builder.
- [x] Phase 2 — indexer, upsert job, event wiring, availability bulk, reindex command, Horizon queue.
- [x] Phase 3 — `CatalogSearchService` for **API v2** (filters/search/sort/min-max). v1 stays MySQL-only (no driver flag).

### Phase 4 — Catalog API v2 (backend done)

- [x] `api/v2` routes + allowlist; independent minimal v2 response.
- [x] Search quality: `ё→е` + stemmer, fuzzy MSM, `{id,name}` entities.
- [x] Dropped ES from v1 / removed `CATALOG_SEARCH_DRIVER`.
- [x] Disjunctive facet aggregations with price-aware counts.
- [x] Sparse facet metadata and minimal current-category resource.
- [x] v2 contract / query DSL tests and manual live-ES verification.
- [ ] Point test front at `/api/v2`.

### Phase 5 — Cutover

- [ ] Shadow-compare MySQL vs ES ids (optional).
- [ ] Staging test front on v2; prod on v1.
- [ ] Prod: cut over Vue to v2 (or keep v1 MySQL until then).
- [ ] Keep v1 MySQL as rollback until confident.
- [ ] Narrow catalog `SearchService` LIKE after cutover.

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
- API v1 catalog stays on MySQL (no ES flag).

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
