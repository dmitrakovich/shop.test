# Belpost batch mailing

Belpost batch mailing connects local order batches to the Belpost business API.
Operators manage batches in Filament, sync orders as Belpost list items,
commit the list, and then generate or download shipping blanks.

## Codepaths

- API config: `src/config/belpost.php`
- Belpost service provider: `src/app/Providers/BelpostServiceProvider.php`
- Low-level API client and actions: `src/app/Libraries/Belpost/**`
- Filament resource: `src/app/Filament/Resources/Departures/Batches/**`
- Batch lifecycle services:
  `src/app/Services/Belpost/BatchMailing/BelpostBatchListService.php`
- Item lifecycle services:
  `src/app/Services/Belpost/BatchMailing/BelpostBatchItemService.php`
- Blank generation/download:
  `src/app/Services/Belpost/BatchMailing/BelpostBatchDocumentService.php`
- Payload mapping:
  `src/app/Services/Belpost/Mappers/BelpostBatchMapper.php` and
  `src/app/Services/Belpost/Mappers/BelpostOrderItemMapper.php`
- Recipient and geo lookup:
  `src/app/Services/Belpost/Recipient/**` and
  `src/app/Services/Belpost/Geo/**`
- Local state sync: `src/app/Services/Belpost/Sync/**`
- Data columns:
  `src/database/migrations/2026_05_18_120000_add_belpost_fields_to_batches_and_orders_tables.php`
  and
  `src/database/migrations/2026_05_19_120000_add_card_number_to_batches_table.php`
- COD email import:
  `src/app/Console/Commands/Payment/BelpostCODParseFromEmailCommand.php` and
  `src/app/Services/Payment/BelpostCODService.php`
- Schedule: `src/routes/console.php`

## Configuration

The Belpost API client is considered configured only when
`BELPOST_API_TOKEN` is present. Admin actions that call Belpost show an error
instead of calling the API when the token is missing.

| Variable | Purpose | Default in code |
| --- | --- | --- |
| `BELPOST_API_BASE_URL` | API host used by `HttpClient`. | `https://api.belpost.by` |
| `BELPOST_API_TOKEN` | Bearer token for API requests. | none |
| `BELPOST_POSTAL_DELIVERY_TYPE` | Default batch shipment type. | `ecommerce_elite` |
| `BELPOST_DIRECTION` | Default direction. | `internal` |
| `BELPOST_PAYMENT_TYPE` | Default payment type. | `electronic_personal_account` |
| `BELPOST_CARD_NUMBER` | Card/account number required by electronic personal account payments. | none |
| `BELPOST_NEGOTIATED_RATE` | Default negotiated-rate toggle. | `false` |
| `BELPOST_NOTIFICATION` | Notification type sent on items. | `5` |
| `BELPOST_FALLBACK_RECIPIENT_EMAIL` | Recipient email fallback when order/user email is empty. | none |
| `BELPOST_SENDER_PHONE` | Sender phone default defined in config; current batch mappers do not read it. | `config('app.phone')` |
| `BELPOST_SENDER_EMAIL` | Sender email default defined in config; current batch mappers do not read it. | `config('app.email')` |
| `BELPOST_SHELF_LIFE_DAYS` | Item `addons.shelf_life` when declared value or valid partial receipt applies. | `10` |
| `BELPOST_ITEM_CATEGORY_ECOMMERCE` | Item category for e-commerce tariffs; must be `0`, `1`, or `2`. | `1` |
| `BELPOST_ITEM_CATEGORY` | Item category for non-e-commerce tariffs; must be `0`, `1`, or `2`. | `0` |
| `BELPOST_POSTAL_ITEMS_IN_OPS` | Adds `postal_items_in_ops` on e-commerce list payloads. | `true` |
| `BELPOST_MAX_COD_WITHOUT_DECLARED_VALUE` | Max COD amount without declared value. | `238` |
| `BELPOST_S10_SERIES_PREFIXES` | Comma-separated allowed S10 series prefixes. Empty means any valid series. | `PC` |
| `BELPOST_S10_SERIAL_MIN` / `BELPOST_S10_SERIAL_MAX` | Optional inclusive S10 serial bounds from the Belpost contract. | none |
| `BELPOST_OMIT_S10CODE_ON_SERIES_MISMATCH` | Omit mismatched S10 codes instead of throwing. | `true` |
| `BELPOST_GEO_DIRECTORY_ENABLED` | Enables Belpost geo-directory address resolution for recipient registration. | `true` |

COD email import uses IMAP settings from `src/config/services.php`:

| Variable | Purpose |
| --- | --- |
| `IMAP_BELPOST_HOST` | Mailbox host. |
| `IMAP_BELPOST_USER` | Mailbox user. |
| `IMAP_BELPOST_PASSWORD` | Mailbox password. |

## Admin workflow

Filament exposes batches under the departures navigation group through
`BatchResource`; with the default admin prefix the route is `/admin/batches`.

1. Create a local batch. Defaults are filled from `config('belpost.defaults')`.
2. Attach orders from the relation manager. Only orders without a batch and
   with a shipment-preparation status are offered in the selector.
3. Create the Belpost list.
4. Sync all orders, or sync individual orders from the relation manager.
5. Commit the Belpost list. After this, list items are no longer editable.
6. Generate blanks, then download the generated ZIP.

Header actions on the edit page map directly to lifecycle services:

| Admin action method | Service call | Effect |
| --- | --- | --- |
| `createBelpostAction()` | `BelpostBatchListService::create()` | Creates the remote list and stores the returned Belpost fields. |
| `updateBelpostAction()` / save hook | `BelpostBatchListService::update()` | Pushes editable batch parameters to Belpost. |
| `syncItemsAction()` | `BelpostBatchItemService::syncAll()` | Creates or updates each attached order as a Belpost list item. |
| `commitBelpostAction()` | `BelpostBatchListService::commit()` | Commits the list and touches `dispatch_date`. |
| `generateBlanksAction()` | `BelpostBatchDocumentService::generateBatchBlanks()` | Starts batch blank generation. |
| `downloadBlanksAction()` | `BelpostBatchDocumentService::download()` | Waits briefly for generated blanks and streams a ZIP. |
| `refreshBelpostAction()` | `BelpostBatchListService::fetch()` | Refreshes local batch, item, and document state from Belpost. |
| `deleteBelpostAction()` | `BelpostBatchListService::delete()` | Deletes the remote list and clears local Belpost fields. |

Per-order relation actions call `BelpostBatchItemService::create()`,
`update()`, or `delete()`. The item sync first ensures a Belpost recipient
exists for the order, then sends the item payload.

## Local state model

Belpost list state is stored on `batches`:

| Column | Meaning |
| --- | --- |
| `belpost_list_id` | Remote Belpost list id; present means the batch is linked. |
| `belpost_status` | Remote status: `uncommitted`, `committed`, or `in_ops`. |
| `belpost_document_id` | Generated document id used for blank downloads. |
| `belpost_sync_error` | Last API error shown on the edit form. |
| `dispatch_date` | Touched when the list is committed. |
| `postal_delivery_type`, `direction`, `payment_type`, `card_number`, `negotiated_rate`, `is_declared_value`, `is_partial_receipt` | Parameters sent in the list payload. |

Belpost item state is stored on `orders`:

| Column | Meaning |
| --- | --- |
| `belpost_item_id` | Remote Belpost list item id. |
| `belpost_s10code` | S10 tracking code returned by Belpost or accepted from an existing track. |

`Batch::isBelpostEditable()` allows edits while the status is empty or
`uncommitted`. `committed` and `in_ops` batches are treated as locked for item
changes. Blank generation is allowed only after the batch is linked and either
committed, in OPS, or has a `dispatch_date`.

## Payload rules and pitfalls

- E-commerce shipment types force `is_partial_receipt` to `false` because the
  API treats partial receipt like attachment declarations that this app does
  not populate.
- E-commerce shipment types force `negotiated_rate` to `0` in the API payload
  and add `postal_items_in_ops` from config.
- The list-level declared-value flag is only sent when the selected shipment
  type supports it. Classic declared-value tariffs encode declared value in the
  tariff itself instead.
- COD amounts use `config('belpost.cod_payment_ids')`, currently payment ids
  `1` and `4`. If COD exceeds `BELPOST_MAX_COD_WITHOUT_DECLARED_VALUE`, the
  batch must have effective declared value or item sync throws before calling
  Belpost.
- When declared value applies, item payloads include
  `addons.declared_value`; the COD addon is capped to the declared value.
- Declared value or tariff-valid partial receipt adds `addons.shelf_life`.
- Electronic notifications require a recipient phone with at least nine
  digits. Every item also needs an email, resolved from order, user, fallback
  config, app email, then mail-from address.
- Existing `belpost_s10code` or a Belpost `OrderTrack` can be reused only when
  it passes S10 format, series, and optional serial-range checks. On allowed
  series mismatch the default behavior is to omit the code and let Belpost
  assign one.
- Belpost item responses update both `orders.belpost_s10code` and the Belpost
  `OrderTrack` record with a `belpost.by` tracking link.

## COD payment import

The scheduled command is:

```text
belpost:cod-parse-from-email
```

It runs hourly between 08:00 and 18:00 from `src/routes/console.php`, but only
parses mail when `Config::findCacheable('auto_order_statuses')['belpost_parse_email']`
is truthy.

When enabled, `BelpostCODService::parseEmail()`:

1. Connects to the IMAP mailbox configured by `IMAP_BELPOST_*`.
2. Looks back over the previous two days through tomorrow.
3. Finds Belpost COD messages for `barocco.by` with the expected attachment
   subject.
4. Saves attachments under `storage/app/public/belpost/cod/{date}/`.
5. Imports Excel rows by matching the track number in column `F` to local order
   tracks and the payment amount in column `C`.
6. Creates a succeeded COD online payment when the order does not already have
   a payment with the same amount.

Manual run example:

```shell
cd src && ./vendor/bin/sail artisan belpost:cod-parse-from-email
```

## Troubleshooting

- `API Belpost is not configured`: set `BELPOST_API_TOKEN`; the API client
  will not issue requests without it.
- `card number is required`: set `BELPOST_CARD_NUMBER` or fill the batch card
  field when using an electronic personal account payment type.
- `batch is already committed`: committed or in-OPS batches cannot be changed;
  create a new batch or refresh state from Belpost.
- `document is still being generated`: blank generation is asynchronous in
  Belpost. Retry the download after generation finishes.
- `recipient phone/email is required`: fix order contact fields or configure a
  recipient email fallback before syncing the item.
- `COD exceeds limit without declared value`: enable declared value on a
  supported e-commerce batch or adjust the shipment/payment setup before
  syncing.
