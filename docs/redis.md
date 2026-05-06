# Redis

## Php setup
```shell
pecl install redis
apt-get install php-redis
```

## Redis setup

/etc/redis/redis.conf

### Включаем AOF (Append Only File) — гарантированное восстановление очередей
```
appendonly yes
appendfsync everysec
save 900 1      # если изменился хотя бы 1 ключ за 900 секунд
save 300 10     # если изменилось >= 10 ключей за 300 секунд
save 60 10000   # если изменилось >= 10000 ключей за 60 секунд
maxmemory-policy noeviction
```

### Эта настройка актуальная для кэша, но не подходит для очередей:
```
appendonly no
maxmemory-policy volatile-lru
save ""
```

### Универсальные настройки
```
maxmemory 4gb
databases 4
tcp-backlog 4096
tcp-keepalive 300
```


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
