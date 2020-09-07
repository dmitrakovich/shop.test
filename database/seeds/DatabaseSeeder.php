<?php

use Illuminate\Database\Seeder;
use App\Product;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(CategorySeeder::class);
        factory(Product::class, 500)->create();
        $this->call(ProductImageSeeder::class);
        $this->call(UrlsSeeder::class);
    }
}
