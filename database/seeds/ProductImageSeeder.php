<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Schema\Blueprint;

class ProductImageSeeder extends Seeder
{
    protected $tableName = 'product_images';
    protected $oldTableName = 'cyizj_jshopping_products_images';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // DB::table('product_images')->truncate();

        // $imgsList = glob(public_path() . '/images/products/*');
        // $productsId = Product::pluck('id');

        // foreach ($productsId as $productId) {
        //     $imgsCount =  mt_rand(2, 6);
        //     while (--$imgsCount) {
        //         $data[] = [
        //             'product_id' => $productId,
        //             'img' => basename(Arr::random($imgsList)),
        //             'sorting' => $imgsCount,
        //             'created_at' => now(),
        //         ];
        //     }
        // }

        // DB::table('product_images')->insert($data);

        if (!Schema::hasTable($this->oldTableName)) {
            throw new Exception("Please, copy old photo table '{$this->oldTableName}'");
        }

        Schema::dropIfExists($this->tableName);
        Schema::rename($this->oldTableName, $this->tableName); 

        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('image_id');
            $table->dropColumn('name');
            $table->renameColumn('image_name', 'img');
            $table->renameColumn('ordering', 'sorting');
        });
    }
}
