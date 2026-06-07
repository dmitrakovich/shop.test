# Catalog product admin

Filament product admin is the backend surface for maintaining catalog product
records used by the external storefront API and internal order workflows.

## Codepaths

- Filament resource: `src/app/Filament/Resources/Products/Products/ProductResource.php`
- Product form: `src/app/Filament/Resources/Products/Products/Schemas/ProductForm.php`
- Product table: `src/app/Filament/Resources/Products/Products/Tables/ProductsTable.php`
- Product model: `src/app/Models/Product.php`
- SKU/brand unique index:
  `src/database/migrations/2026_05_28_120000_add_unique_sku_brand_id_to_products_table.php`

## Admin workflow

Products are managed in Filament under the products navigation group. With the
default admin prefix, the resource is available at `/admin/products`.

The form requires the fields that define a sellable catalog item, including:

- `sku` (`Артикул`)
- `brand_id` (`Бренд`)
- `category_id`
- active sizes
- collection and season
- price fields

Product media is managed through Spatie Media Library uploads on the `media`
disk. Existing media rows can also be marked as image-style photos through the
media properties repeater on the edit page.

## SKU and brand uniqueness

`sku` is unique only within a brand, not globally. This allows two brands to use
the same article while preventing duplicate product rows for the same brand.

Example:

| SKU | Brand | Allowed? | Reason |
| --- | --- | --- | --- |
| `A-100` | `Brand One` | Yes | First row for that brand. |
| `A-100` | `Brand Two` | Yes | Same SKU under a different brand. |
| `A-100` | `Brand One` | No | Duplicate `(sku, brand_id)` pair. |

The invariant is enforced in two places:

1. The Filament form validates `sku` with a unique rule scoped to the selected
   `brand_id` and shows the duplicate message before save.
2. The database has a unique index on `products.sku` + `products.brand_id`, so
   concurrent saves or imports cannot bypass the constraint.

Because `products` uses soft deletes and the unique index does not include
`deleted_at`, a soft-deleted product still reserves its `(sku, brand_id)` pair.
Restore or change the old record before creating a replacement with the same
SKU and brand.

## Migration and troubleshooting

Before applying the unique-index migration to an environment with existing
catalog data, resolve duplicate pairs. The migration file includes SQL for
finding duplicates:

```sql
SELECT sku, brand_id, COUNT(*) AS cnt, GROUP_CONCAT(id ORDER BY id) AS product_ids
FROM products
GROUP BY sku, brand_id
HAVING COUNT(*) > 1;
```

Common failures:

- `Товар с таким артикулом и брендом уже существует.`: choose a different SKU,
  select the correct brand, or restore/update the existing product row.
- Database duplicate-key error on save/import: another process inserted the
  same `(sku, brand_id)` pair after validation; reload the product list and
  reconcile the duplicate.
