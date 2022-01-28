<?php

use Diglactic\Breadcrumbs\Breadcrumbs;

// Index
Breadcrumbs::for('index', function ($trail) {
    $trail->push('Главная', route('index-page'));
});


#region Shop
// Index > catalog > category
Breadcrumbs::for('category', function ($trail, $category) {
    if ($category->parentCategory) {
        $trail->parent('category', $category->parentCategory);
    } else {
        $trail->parent('index');
    }
    $trail->push($category->title, $category->getUrl());
});

// Index > catalog > category > product
Breadcrumbs::for('product', function ($trail, $product) {
    $trail->parent('category', $product->category);
    $trail->push($product->extendedName());
});

// Index > cart
Breadcrumbs::for('cart', function ($trail) {
    $trail->parent('index');
    $trail->push('Корзина');
});
#endregion


#region Online shoping
// Index > online-shopping
Breadcrumbs::for('online-shopping', function ($trail) {
    $trail->parent('index');
    $trail->push('Онлайн покупки', url('online-shopping'));
});

// Index > online-shopping > {item}
Breadcrumbs::for('info-page', function ($trail, $item) {
    $trail->parent('online-shopping');
    $trail->push($item['name'], route('info', $item['slug']));
});

// Index > shops
Breadcrumbs::for('static-shops', function ($trail) {
    $trail->parent('index');
    $trail->push('Магазины', route('static-shops'));
});
#endregion


// Index > feedbacks
Breadcrumbs::for('feedbacks', function ($trail) {
    $trail->parent('index');
    $trail->push('Отзывы', route('feedbacks'));
});


#region Dashboard
// Index > dashboard
Breadcrumbs::for('dashboard', function ($trail) {
    $trail->parent('index');
    $trail->push('Личный кабинет', url('dashboard'));
});

// Index > dashboard > orders
Breadcrumbs::for('dashboard-orders', function ($trail) {
    $trail->parent('dashboard');
    $trail->push('Мои заказы', route('orders.index'));
});

// Index > dashboard > saved
Breadcrumbs::for('dashboard.favorites', function ($trail) {
    $trail->parent('dashboard');
    $trail->push('Избранное', route('dashboard.favorites'));
});

// Index > dashboard > profile
Breadcrumbs::for('dashboard-profile', function ($trail) {
    $trail->parent('dashboard');
    $trail->push('Мои данные', route('dashboard-profile'));
});

// Index > dashboard > card
Breadcrumbs::for('dashboard-card', function ($trail) {
    $trail->parent('dashboard');
    $trail->push('Карта клиента', route('dashboard-card'));
});
#endregion
