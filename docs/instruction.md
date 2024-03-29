# Первоначальная настройка

* Установить git
* Установить composer
* Установить node.js

## Клонируем проект с гита
```shell
git clone https://dmitrakovich@github.com/dmitrakovich/shop.test.git
```

Настроить окружение в файле .env

## Настраиваем .htaccess в корне сайта
```
RewriteEngine on 

RewriteCond %{HTTP_HOST} ^(www.)?barocco.by$

RewriteCond %{REQUEST_URI} !^/public/ 

RewriteCond %{REQUEST_FILENAME} !-f 
RewriteCond %{REQUEST_FILENAME} !-d 

RewriteRule ^(.*)$ /public/$1 
RewriteCond %{HTTP_HOST} ^(www.)?barocco.by$ 
RewriteRule ^(/)?$ public/index.php [L]
```

## Установить продакшн зависимости и опимизировать загрузку
```shell
composer install --optimize-autoloader --no-dev
npm i
```

## Настраиваем .env
```shell
cp .env.example .env
php artisan key:generate
```

## Закэшировать конфиги, роуты и шаблоны (сбросить старый кэш)
```shell
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Настройка БД
### Создаем БД barocco
```sql
CREATE SCHEMA `barocco` DEFAULT CHARACTER SET utf8mb4_unicode_ci;
```
### Запускаем миграцию
```shell
php artisan migrate
```
Внимание! Если версия MySQL меньше 5.7.7 или MariaDB меньше 10.2.2, то следует добавить **Schema::defaultStringLength** в **AppServiceProvider**:
```php
use Illuminate\Support\Facades\Schema;

/**
 * Bootstrap any application services.
 *
 * @return void
 */
public function boot()
{
    Schema::defaultStringLength(191);
}
```

## Собираем фронтенд
```shell
nmp run production
```

// перенести эту инстркцию целиком в докер и удалить её
// sftp -P {port} {user}@{host}:
// get -r public_html/src/storage/app/public/products/3/34/349/ /home/ondemand-dev/docker-app-images/www/shop.test/src/storage/app/public/products/3/34
// get -r public_html/src/storage/app/public/products/ /mnt/d/my-projects/shop.test/src/storage/app/public/


// generate ssh key
```shell
ssh-keygen -t ed25519 -C "dmitrakovich.andrey@yandex.by"
ssh-keygen -t rsa -b 4096 -C "dmitrakovich.andrey@yandex.by"
```
