# Laravel-admin

documentation: https://laravel-admin.org/docs/

## Первоначальная настройка админки:
```shell
php artisan admin:install
php artisan db:seed --class=AdminPanelSeeder
```

## Использование:

## 1) Add controller
Use the following command to create a controller for `App\User` model
```shell
php artisan admin:make UserController --model=App\Models\User
```

## 2) Add route
Add a route in `app/Admin/routes.php`：
```php
$router->resource('users', UserController::class);
```

## 3) Add menu item
open http://localhost:3000/admin/auth/menu, add menu link and refresh the page, then you can find a link item in left menu bar.

## 4) Write CURD page logic
The controller `app/Admin/Controllers/UserController.php` created by the `admin:make` command is as follows
