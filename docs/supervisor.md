## Installing Supervisor

Supervisor is a process monitor for the Linux operating system, and will automatically restart your `queue:work` processes if they fail. To install Supervisor on Ubuntu, you may use the following command:

```shell
sudo apt-get install supervisor
```

## Configuring Supervisor

Supervisor configuration files are typically stored in the `/etc/supervisor/conf.d` directory. Create a `laravel-worker.conf` file that starts and monitors `queue:work` processes:

```properties
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/www-user/www/api.barocco.by/current/artisan queue:work --queue=high,default,low --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-user
numprocs=1
redirect_stderr=true
stdout_logfile=/home/www-user/www/api.barocco.by/current/storage/logs/worker/stdout.log
stopwaitsecs=3600
stdout_logfile_maxbytes=50MB
stdout_logfile_backups=10

[program:pixel]
process_name=%(program_name)s_%(process_num)02d
command=php /home/www-user/www/api.barocco.by/current/artisan queue:work --queue=pixel --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-user
numprocs=1
redirect_stderr=true
stdout_logfile=/dev/null
stopwaitsecs=3600

[program:media]
process_name=%(program_name)s_%(process_num)02d
command=php /home/www-user/www/api.barocco.by/current/artisan queue:work --queue=media --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-user
numprocs=1
redirect_stderr=true
stdout_logfile=/home/www-user/www/api.barocco.by/current/storage/logs/worker/stdout.log
stopwaitsecs=3600
stdout_logfile_maxbytes=50MB
stdout_logfile_backups=10
```

## Starting Supervisor

```shell
sudo supervisorctl reread
 
sudo supervisorctl update
 
sudo supervisorctl start laravel-worker:*
```

## Laravel Horizon (same queue topology)

The application ships with [Laravel Horizon](https://laravel.com/docs/horizon); worker layout mirrors the three programmes above (`laravel-worker`: `high`, `default`, `low`, plus `one_c` from the codebase; `pixel`; `media`). See `src/config/horizon.php`.

Requirements: Redis available for Horizon workers (supervisors stay on the `redis` queue connection — not `failover`), `QUEUE_CONNECTION=failover` in `.env` for app dispatch when using the project defaults, Scheduler running (includes `horizon:snapshot`).

With Supervisor, prefer a **single** programme instead of three `queue:work` lines, for example:

```properties
[program:horizon]
command=php /path/to/current/artisan horizon
autostart=true
autorestart=true
user=www-user
redirect_stderr=true
stdout_logfile=/path/to/current/storage/logs/worker/horizon.log
stopwaitsecs=3600
```
