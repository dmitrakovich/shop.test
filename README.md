# Barocco backend

Laravel backend for the Barocco e-commerce storefront. The main Vue
storefront is maintained outside this repository; this repo provides the API,
admin panels, catalog/order domain logic, queues, scheduled jobs, and deploy
configuration.

## Stack and layout

- Laravel 12 on PHP 8.5.
- Application root: [`src/`](src/).
- Local services in Sail: MySQL and Redis (`src/docker-compose.yml`).
- Primary admin: Filament at `/admin` (`src/app/Filament/**`).
- Legacy admin: linked from Filament under `/old-admin`.
- API v1 routes: `api/v1/*` from `src/routes/api.php`.
- Operational and subsystem docs: [`docs/README.md`](docs/README.md).

## Local workflow

Run project commands from `src/` and use Sail for Composer, Artisan, Pint, and
PHPStan so the PHP version and extensions match the app container. If `vendor/`
is missing, install Composer dependencies first in an environment with PHP 8.5
and access to the private VCS repositories listed in `src/composer.json`.

Once `vendor/bin/sail` exists:

```shell
cd src
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

After Sail is available, prefer it for dependency changes:

```shell
cd src && ./vendor/bin/sail composer install
```

Common checks:

```shell
cd src && ./vendor/bin/sail composer lint
cd src && ./vendor/bin/sail composer phpstan
cd src && ./vendor/bin/sail test
```

## Environment notes

Use [`src/.env.example`](src/.env.example) as the source of truth for local
variables. Notable defaults:

- `DB_HOST=mysql` and `REDIS_HOST=redis` for Sail services.
- `QUEUE_CONNECTION=failover`, which dispatches to Redis first and falls back
  to database on enqueue failures.
- `HORIZON_PATH=admin/horizon`; Horizon is protected by the admin guard.
- `SENTRY_LARAVEL_DSN` is optional.

Never commit real secrets, API tokens, DSNs, database passwords, or SSH keys.

## Documentation map

Start with [`docs/README.md`](docs/README.md) for subsystem docs and runbooks.
Recently documented areas include:

- [`docs/belpost-batch-mailing.md`](docs/belpost-batch-mailing.md) - Belpost
  batch API workflow, Filament operations, shipment constraints, blanks, and
  COD import.
- [`docs/product-rating.md`](docs/product-rating.md) - catalog rating
  algorithms, admin workflow, scheduler, and storefront sort behavior.
