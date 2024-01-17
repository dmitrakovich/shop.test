# Redis

## Php setup
```shell
pecl install redis
apt-get install php-redis
```

## Redis setup

/etc/redis/redis.conf

* maxmemory 4gb
* maxmemory-policy volatile-lru
* databases 4
* tcp-backlog 4096
* tcp-keepalive 300
* save ""


## Commands (root)

Redis Command Line Interface:
```shell
redis-cli
```

Start Redis:
```shell
systemctl start redis-server
```

Status Redis service:
```shell
systemctl status redis-server.service
```

Enable autoload for Redis:
```shell
systemctl status redis-server.service
```

Restart:
```shell
systemctl restart redis-server
```
