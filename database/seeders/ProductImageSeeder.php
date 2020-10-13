<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
