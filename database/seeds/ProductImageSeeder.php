<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $imgsList = glob(public_path() . '/images/products/*');

        for ($productId = 1; $productId <= 500; $productId++) {
            $imgsCount =  mt_rand(2, 6);
            while (--$imgsCount) {
                $data[] = [
                    'product_id' => $productId,
                    'img' => basename(Arr::random($imgsList)),
                    'sorting' => $imgsCount,
                    'created_at' => now(),
                ];
            }
        }

        DB::table('product_images')->insert($data);
    }
}
