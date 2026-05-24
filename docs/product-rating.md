# Product rating and catalog sorting

Product ratings control the default product order in catalog listings and
several recommendation sliders. The current implementation stores two signed
integer scores on each product:

- `products.rating` - default popularity score.
- `products.newness_rating` - score used by the "newness" sort.

## Codepaths

- Rating algorithm model: `src/app/Models/RatingAlgorithm.php`
- Factor list and coefficient column names:
  `src/app/Enums/Product/RatingFactor.php`
- Filament resource:
  `src/app/Filament/Resources/Products/RatingAlgorithms/**`
- Recalculation job: `src/app/Jobs/UpdateProductsRatingJob.php`
- Console command: `src/app/Console/Commands/UpdateRating.php`
- Schedule: `src/routes/console.php`
- Catalog sort enum: `src/app/Enums/Product/ProductSort.php`
- Product ordering scope: `src/app/Models/Product.php::scopeSorting`
- API catalog route: `GET /api/v1/catalog/{path?}` from `src/routes/api.php`

## Admin workflow

Rating algorithms are managed in Filament under the products navigation group
as "Rating algorithms". Each algorithm has a name and integer coefficients for
all factors listed below.

The list page provides two header actions:

1. **Settings** - choose the algorithm used for popularity, the algorithm used
   for newness, and optional product/category boost or penalty lists.
2. **Recalculate rating** - runs `UpdateProductsRatingJob::dispatchSync()` in
   the current request and updates product scores immediately.

The settings are stored in the `configs` table with key `rating`.

## Factors and scoring

`UpdateProductsRatingJob` loads all non-deleted products with non-zero price,
builds normalized factor scores, and calculates:

```text
rating = sum(factor_score * popularity_algorithm_coefficient)
newness_rating = sum(factor_score * newness_algorithm_coefficient)
```

Both values are rounded to integers and written back to `products` in chunks of
1000 rows.

| Factor | Source | Score behavior |
| --- | --- | --- |
| Views | Yandex Metrika `ym:s:productImpressionsUniq`, last 30 days through yesterday | Min/max normalized to 0..100. |
| Carts | Yandex Metrika `ym:s:productBasketsUniq`, last 7 days through yesterday | Min/max normalized to 0..100. |
| Purchases | Yandex Metrika `ym:s:productPurchasedUniq`, last 30 days through yesterday | Min/max normalized to 0..100. |
| Price | Current product price | Min/max normalized to 0..100. Use a negative coefficient to favor lower prices. |
| Discount | Percent difference between `old_price` and `price` | Min/max normalized to 0..100; zero when `old_price` is empty or not greater than `price`. |
| Category up | Category IDs in rating settings | `100` when matched, otherwise `0`. |
| Category down | Category IDs in rating settings | `-100` when matched, otherwise `0`. |
| Season | Related season marked actual | `100` when actual, otherwise `0`. |
| Created at | Product age in days | `100 / sqrt(days + 1)`, so newer products score higher. |
| Product up | Product IDs in rating settings | `100` when matched, otherwise `0`. |
| Product down | Product IDs in rating settings | `-100` when matched, otherwise `0`. |

The rating columns are signed integers, so penalties can push final scores
below zero.

## Scheduler and manual recalculation

The scheduler runs the rating command four times per day:

```text
15 5,11,17,23 * * * php artisan rating:update
```

Run it manually through Sail when validating changes locally:

```shell
cd src && ./vendor/bin/sail artisan rating:update
```

The job calls the Yandex Metrika API with:

- `YANDEX_COUNTER_ID`
- `YANDEX_TOKEN`

If either value is missing or the API returns an error payload, recalculation
throws an exception and product scores are not updated.

## Storefront behavior

Catalog requests accept a `sort` query parameter parsed by
`FilterRequest::getSorting()`:

| Query value | Ordering |
| --- | --- |
| `rating` or omitted | `rating DESC, id DESC` |
| `newness` | `newness_rating DESC, id DESC` |
| `price-up` | `price ASC, id ASC` |
| `price-down` | `price DESC, id DESC` |

Recommendation sliders in `SliderService` also order several product sets by
`rating DESC`.

## Migration notes

The rating algorithm migration creates `rating_algorithms`, adds
`products.newness_rating`, and converts legacy `configs.rating` algorithm data
when present. A follow-up migration makes `products.rating` signed to support
negative penalties.

When changing factors or score semantics:

- Update `RatingFactor`, `RatingAlgorithm::coefficientColumns()`, the Filament
  form, and `UpdateProductsRatingJob` together.
- Keep the catalog sort enum and public sort query values stable unless the
  external storefront is updated at the same time.
- Recalculate ratings after deploying coefficient or factor changes.
