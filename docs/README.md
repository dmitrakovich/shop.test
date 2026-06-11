# Technical documentation

This directory holds project-specific docs for the Barocco Laravel backend.
Keep pages concise, source-backed, and focused on workflows that are hard to
infer from code alone.

## Start here

| Audience | Page | Covers |
| --- | --- | --- |
| Developers | [Repository README](../README.md) | Stack, app layout, local Sail workflow, quality commands. |
| Catalog/admin | [Catalog product admin](catalog-product-admin.md) | Filament product workflow, SKU/brand uniqueness, migration troubleshooting. |
| Catalog/admin | [Product rating](product-rating.md) | Rating algorithms, Filament workflow, scheduled recalculation, storefront sort behavior. |
| Promo/admin | [Promo short links](promo-short-links.md) | Filament short-link generator, UTM normalization, redirect counters. |
| Departures/operations | [Belpost batch mailing](belpost-batch-mailing.md) | Filament batch workflow, Belpost API sync, shipment payload rules, blanks, COD import. |
| Operations | [Server setup](server-setup.md) | Host user, PHP-FPM/nginx, SQL Server driver, deploy skeleton, cron, supervisor links. |
| Operations | [Supervisor](supervisor.md) | Queue workers and Horizon process supervision. |
| Operations | [Redis](redis.md) | Redis installation notes. |
| Operations | [Media](media.md) | Media conversion notes. |
| Operations | [SSH tunneling](ssh-tunneling.md) | Manual SSH tunnel setup notes. |
| Operations | [MS SQL Server connection](ms-sql-server-connection.md) | SQL Server driver installation notes. |

## Documentation rules

- Verify behavior against `src/` before changing docs.
- Prefer updating an existing page when the audience and workflow already
  match.
- Include the codepaths that define the behavior so future readers can verify
  drift quickly.
- Keep secrets and production credentials out of examples.
