## Add user
```shell
# create user with home dir
sudo useradd -m -s /bin/bash www-root
# create password
sudo passwd www-root
# add to www-data group & vice versa
usermod -a -G www-data www-root
usermod -a -G www-root www-data
```


## On local machine:
```shell
# generate key for deploy
ssh-keygen -m PEM -f ~/.ssh/deploy -t rsa -b 4096 -C "dmitrakovich.andrey@yandex.by" 

# copy local ssh key to authorized_keys
ssh-copy-id -i ~/.ssh/id_rsa.pub -p 2222 www-root@178.159.45.67

# copy deploy ssh key to authorized_keys
ssh-copy-id -i ~/.ssh/deploy.pub -p 2222 www-root@178.159.45.67
```
Then copy user, port, path & private key (base64) to GitHub Environments.


## Files structure
```shell
ln -s /home/www-root/deploy/current/public /var/www/barocco.by
```


## Add certs
```shell 
/etc/nginx/ssl-certs/barocco.by.crt # All certs in 1 file 
/etc/nginx/ssl-certs/barocco.by.key # Rsa private key
```
Generate key for ssl_dhparam
```shell
openssl dhparam -out /etc/ssl/certs/dhparam4096.pem 4096
```


## Nginx setup
In nginx.conf change user to `www-root` and add site config ([example](https://github.com/dmitrakovich/shop.test/blob/master/docs/nginx.conf.md)).


## Php setup
Install php:
```shell
apt-get install php-common php-mysql php-mbstring php-xml php-zip php-gd php-cli php-fpm
```
In /etc/php/8.1/fpm/pool.d/www.conf change user to `www-root`
```properties
user = www-root
group = www-root

listen.owner = www-root
listen.group = www-root
```

## Restart php-fpm & nginx
```shell
service php8.1-fpm restart
systemctl restart nginx
```

## Create DB
```sql
CREATE USER 'username'@'localhost' IDENTIFIED BY 'password';
CREATE DATABASE dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON dbname.* TO 'username'@'localhost';
```


## Config .env


## Run deploy


## Config crontab
```
* * * * * cd /path-to-your-deploy-folder/current && php artisan schedule:run >> /dev/null 2>&1
```


## Config supervisor (Queues)
[instruction](https://github.com/dmitrakovich/shop.test/blob/master/docs/supervisor.md).
