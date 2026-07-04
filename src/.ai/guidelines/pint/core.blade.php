# Laravel Pint Code Formatter

- **Always** pass the project config: `--config ../.formatters/pint.json` (when run from `src/`). There is no `src/pint.json`; Pint without `--config` uses only the default `laravel` preset and skips project rules (including `fully_qualified_strict_types`).
- Prefer `cd src && ./vendor/bin/sail composer lint` — the Composer `lint` script already includes the correct config.
- After editing PHP files, run `./vendor/bin/sail bin pint --config ../.formatters/pint.json --dirty --format agent` (or `composer lint` on the touched paths).
- Do not run bare `pint`, `sail bin pint --dirty`, or `sail bin pint --format agent` without `--config ../.formatters/pint.json`.
