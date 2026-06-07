# Promo short links

Short links let operators turn Barocco storefront URLs into compact redirect
URLs with normalized UTM tags. The workflow now lives in Filament and records
basic usage counters for each generated link.

## Codepaths

- Filament resource:
  `src/app/Filament/Resources/Promo/ShortLinks/ShortLinkResource.php`
- Generator page:
  `src/app/Filament/Resources/Promo/ShortLinks/Pages/ListShortLinks.php`
- Table columns:
  `src/app/Filament/Resources/Promo/ShortLinks/Tables/ShortLinksTable.php`
- Short-link model: `src/app/Models/ShortLink.php`
- Redirect route: `src/routes/redirect.php`, required from `src/routes/web.php`
- Usage-tracking migration:
  `src/database/migrations/2026_05_24_170000_add_usage_tracking_to_short_links_table.php`
- Source-to-UTM mapping: `src/app/Enums/Order/OrderMethod.php`

## Admin workflow

Short links are managed in Filament under the promo navigation group. With the
default admin prefix, the page is available at `/admin/short-links`.

1. Paste a storefront URL into `Исходная ссылка`.
   The form accepts URLs that start with `https://barocco.by`.
2. Optionally choose an order source. The source controls the generated
   `utm_source`, `utm_medium`, and `utm_campaign` values.
3. Review `Сгенерированная ссылка`; it is the destination URL that will be
   stored behind the short code.
4. Click `Сгенерировать короткую ссылку`.
5. Copy the generated `/lnk/{code}` URL from the form or table.

The resource intentionally has no standard create, edit, or delete actions.
Records are created only through the generator form, and the table is read-only.

## UTM normalization

`ListShortLinks::buildTrackedLink()` parses the submitted URL and removes any
existing UTM values for:

- `utm_source`
- `utm_medium`
- `utm_campaign`
- `utm_content`
- `utm_term`

When a source is selected, `OrderMethod::utmSources()` provides
`utm_source`, `utm_medium`, and the base campaign. The generator appends
`link` to the campaign value.

Every generated destination also includes:

| Parameter | Value |
| --- | --- |
| `utm_content` | Current admin username from the `admin` guard, or an empty string. |
| `utm_term` | Current date in `ymd` format. |

Example shape for an Instagram source created on 2026-06-07:

```text
https://barocco.by/catalog?utm_source=instagram&utm_medium=social&utm_campaign=managerlink&utm_content=admin&utm_term=260607
```

## Redirect behavior and counters

Public short links use this route:

```text
GET /lnk/{short_link}
```

The route resolves `ShortLink` by the `short_link` column, increments
`hits_count`, sets `last_used_at` to the current time, and redirects to
`full_link`. Unknown codes redirect to the `shop` route.

`ShortLink::createShortLink()` uses `firstOrCreate()` by `full_link`, so
generating the same tracked destination again reuses the existing short code
instead of creating a duplicate row.

## Troubleshooting

- Input URL rejected: use the canonical `https://barocco.by...` storefront URL.
- Generated short code already exists for the destination: this is expected
  when the normalized tracked URL matches an existing `full_link`.
- Click count does not change during copy/paste checks: only visits through
  `/lnk/{short_link}` call `recordHit()`; opening `full_link` directly does not
  update counters.
