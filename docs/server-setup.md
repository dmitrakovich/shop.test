## Add user
```shell
# create user with home dir
sudo useradd -m -s /bin/bash www-user
# create password
sudo passwd www-user
# add to www-data group & vice versa
usermod -a -G www-data www-user
usermod -a -G www-user www-data
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
List and keep note of existing PHP packages:
```shell
dpkg -l | grep php | tee packages.txt
```

## Install New PHP 8.5 Packages:
```shell
# Add the ondrej/php repository.
sudo apt update
sudo apt install -y software-properties-common
sudo LC_ALL=C.UTF-8 add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP.
sudo apt install php8.5-common php8.5-cli php8.5-fpm php8.5-{curl,mysql,mbstring,intl,xml,redis,imap,zip,gd}
```

## In nginx config (/etc/nginx/sites-enabled/barocco.by):
```diff
- fastcgi_pass unix:/run/php/php8.1-fpm.sock;
+ fastcgi_pass unix:/run/php/php8.3-fpm.sock;
```

## Microsoft Drivers for PHP for SQL Server ([tutorial](https://learn.microsoft.com/en-us/sql/connect/php/installation-tutorial-linux-mac))

```shell
sudo pecl install sqlsrv
sudo pecl install pdo_sqlsrv
sudo su
printf "; priority=20\nextension=sqlsrv.so\n" > /etc/php/8.3/mods-available/sqlsrv.ini
printf "; priority=30\nextension=pdo_sqlsrv.so\n" > /etc/php/8.3/mods-available/pdo_sqlsrv.ini
exit
sudo phpenmod sqlsrv pdo_sqlsrv
```

## Migrate Configuration
- [/etc/php/8.3/fpm/php.ini](https://github.com/dmitrakovich/shop.test/blob/master/docs/php/php.ini)
- [/etc/php/8.3/fpm/pool.d/www.conf](https://github.com/dmitrakovich/shop.test/blob/master/docs/php/www.conf)

## Restart php-fpm & nginx
```shell
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
```

## Remove old PHP Versions
```shell
sudo apt purge php8.1*
```

## Create DB
```shell
sudo mysql -e "
CREATE DATABASE dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'username'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON bar.* TO 'username'@'%';
FLUSH PRIVILEGES;
"
```


## Config .env


## Run deploy


## Config crontab
```
* * * * * cd /path-to-your-deploy-folder/current && php artisan schedule:run >> /dev/null 2>&1
```


## Config supervisor (Queues)
[instruction](https://github.com/dmitrakovich/shop.test/blob/master/docs/supervisor.md).
