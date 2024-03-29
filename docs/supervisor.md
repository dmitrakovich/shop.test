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
command=php /home/www-root/deploy/current/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-root
numprocs=3
stdout_logfile=/home/www-root/deploy/current/storage/logs/worker/stdout.log
stderr_logfile=/home/www-root/deploy/current/storage/logs/worker/stderr.log
stopwaitsecs=3600
stdout_logfile_maxbytes=50MB
stdout_logfile_backups=10
stderr_logfile_maxbytes=50MB
stderr_logfile_backups=10
```

## Starting Supervisor

```shell
sudo supervisorctl reread
 
sudo supervisorctl update
 
sudo supervisorctl start laravel-worker:*
```
