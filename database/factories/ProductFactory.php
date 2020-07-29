<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Product;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(Product::class, function (Faker $faker) {
    $title = $faker->unique()->sentence(mt_rand(1, 4));
    $createdAt = $faker->dateTimeBetween('-3 months');

    return [
        'category_id'   => mt_rand(1, 12),
        'slug'          => Str::slug($title),
        'title'         => $title,
        'created_at'    => $createdAt,
        'updated_at'    => $createdAt,
    ];
});
