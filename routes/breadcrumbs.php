<?php

// Index
Breadcrumbs::for('index', function ($trail) {
    $trail->push('Главная', route('index-page'));
});



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

/*
// Index > Blog
Breadcrumbs::for('blog', function ($trail) {
    $trail->parent('index');
    $trail->push('Blog', route('blog'));
});

// Index > Blog > [Category]
Breadcrumbs::for('category', function ($trail, $category) {
    $trail->parent('blog');
    $trail->push($category->title, route('category', $category->id));
});

// Index > Blog > [Category] > [Post]
Breadcrumbs::for('post', function ($trail, $post) {
    $trail->parent('category', $post->category);
    $trail->push($post->title, route('post', $post->id));
});*/


/*
// тут должен быть массив предков 
Breadcrumbs::for('category', function ($trail, $category) {
    $trail->parent('blog');

    foreach ($category->ancestors as $ancestor) {
        $trail->push($ancestor->title, route('category', $ancestor->id));
    }

    $trail->push($category->title, route('category', $category->id));
});

// тут должен быть вложеннный массив предков
Breadcrumbs::for('category', function ($trail, $category) {
    if ($category->parent) {
        $trail->parent('category', $category->parent);
    } else {
        $trail->parent('blog');
    }

    $trail->push($category->title, route('category', $category->slug));
});*/

// {{ Breadcrumbs::render('category', $category) }} // вывод в шаблоне+