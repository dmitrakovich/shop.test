<?php

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
    $trail->push($product->getFullName());
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

// Index > online-shopping > instruction
Breadcrumbs::for('static-instruction', function ($trail) {
    $trail->parent('online-shopping');
    $trail->push('Как заказать', route('static-instruction'));
});

// Index > online-shopping > payment
Breadcrumbs::for('static-payment', function ($trail) {
    $trail->parent('online-shopping');
    $trail->push('Оплата', route('static-payment'));
});

// Index > online-shopping > delivery
Breadcrumbs::for('static-delivery', function ($trail) {
    $trail->parent('online-shopping');
    $trail->push('Доставка', route('static-delivery'));
});

// Index > online-shopping > return
Breadcrumbs::for('static-return', function ($trail) {
    $trail->parent('online-shopping');
    $trail->push('Возврат', route('static-return'));
});

// Index > online-shopping > installments
Breadcrumbs::for('static-installments', function ($trail) {
    $trail->parent('online-shopping');
    $trail->push('Рассрочка', route('static-installments'));
});

// Index > shops
Breadcrumbs::for('static-shops', function ($trail) {
    $trail->parent('index');
    $trail->push('Магазины', route('static-shops'));
});
#endregion


#region Dashboard
// Index > dashboard
Breadcrumbs::for('dashboard', function ($trail) {
    $trail->parent('index');
    $trail->push('Личный кабинет', url('dashboard'));
});

// Index > dashboard > orders
Breadcrumbs::for('dashboard-orders', function ($trail) {
    $trail->parent('dashboard');
    $trail->push('Мои заказы', route('dashboard-orders'));
});

// Index > dashboard > saved
Breadcrumbs::for('dashboard-saved', function ($trail) {
    $trail->parent('dashboard');
    $trail->push('Избранное', route('dashboard-saved'));
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