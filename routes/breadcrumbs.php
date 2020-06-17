<?php

// Index
Breadcrumbs::for('index', function ($trail) {
    $trail->push('Главная', route('index-page'));
});




// Index > kak-zakazat
Breadcrumbs::for('kak-zakazat', function ($trail) {
    $trail->parent('index');
    $trail->push('Онлайн покупки', url('kak-zakazat'));
});

// Index > kak-zakazat > kak-zakazat2
Breadcrumbs::for('static-kak-zakazat', function ($trail) {
    $trail->parent('kak-zakazat');
    $trail->push('Как заказать', route('static-kak-zakazat'));
});

// Index > kak-zakazat > kak-zakazat2
Breadcrumbs::for('static-payment', function ($trail) {
    $trail->parent('kak-zakazat');
    $trail->push('Оплата', route('static-payment'));
});


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