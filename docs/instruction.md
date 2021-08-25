# Первоначальная настройка

* Установить git
* Установить composer
* Установить node.js

## Клонируем проект с гита
```shell
git clone https://default-089@github.com/default-089/shop.test.git
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