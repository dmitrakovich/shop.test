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