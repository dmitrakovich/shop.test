# Media conversions

The app uses Spatie Media Library for product and content media. Generated
conversions are queued through `App\Jobs\Media\PerformConversionsJob`, which
always targets the `media` queue.

## Local setup

Create the public storage symlink through Sail:

```shell
cd src && ./vendor/bin/sail artisan storage:link
```

The default queue connection is `failover` (`redis` first, then `database` on
enqueue failure). Horizon has a dedicated production supervisor for the
`media` queue in `src/config/horizon.php`.

For local debugging:

```shell
cd src && ./vendor/bin/sail artisan horizon
```

If Redis was unavailable when conversion jobs were dispatched, failover may
have written jobs to the database queue. Drain those fallback jobs explicitly:

```shell
cd src && ./vendor/bin/sail artisan queue:work database --queue=media
```

## Source paths

- Queue name enum: `src/app/Enums/Queue.php`
- Conversion job: `src/app/Jobs/Media/PerformConversionsJob.php`
- Queue failover behavior: `src/config/queue.php`
- Horizon media supervisor: `src/config/horizon.php`