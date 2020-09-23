<?php

use App\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('product_attributes')->truncate();

        $products = Product::all();

        foreach ($products as $product) {
            $slug = 'barocco-' . $product->id;            
            if (strlen($product['alias_ru-RU']) > 4 && strlen($product['alias_ru-RU']) <= 36) {
                $slug = $product['alias_ru-RU'];
            }
            $product->slug = Str::slug($slug);
            $product->save();
        }
    }
}
