<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Schema\Blueprint;

class ProductSeeder extends Seeder
{
    protected $tableName = 'products';
    protected $oldTableName = 'cyizj_jshopping_products';
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!Schema::hasTable($this->oldTableName)) {
            throw new Exception("Please, copy old product table '{$this->oldTableName}'");
        }

        Schema::dropIfExists($this->tableName);
        Schema::rename($this->oldTableName, $this->tableName);

        Schema::table($this->tableName, function (Blueprint $table) {
            $table->datetime('product_date_added')->default(NULL)->change();
            $table->datetime('date_modify')->default(NULL)->change();

            $table->renameColumn('product_id', 'id');

            $table->uuid('slug')->unique()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });


        DB::update("UPDATE {$this->tableName} SET created_at = product_date_added, updated_at = date_modify
            WHERE product_date_added <> '0000-00-00 00:00:00' AND date_modify  <> '0000-00-00 00:00:00'");

        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('parent_id');
            $table->dropColumn('product_ean');
            $table->dropColumn('product_quantity');
            $table->dropColumn('unlimited');
            $table->dropColumn('product_availability');
            $table->dropColumn('product_date_added');
            $table->dropColumn('date_modify');
            // $table->dropColumn('product_publish');
            $table->dropColumn('product_tax_id');
            $table->dropColumn('currency_id');
            $table->dropColumn('product_template');
            $table->dropColumn('product_url');

            $table->dropColumn('name_en-GB');
            $table->dropColumn('alias_en-GB');
            $table->dropColumn('short_description_en-GB');
            $table->dropColumn('description_en-GB');
            $table->dropColumn('meta_title_en-GB');
            $table->dropColumn('meta_description_en-GB');
            $table->dropColumn('meta_keyword_en-GB');
        });

        $this->call(ProductAttributeSeeder::class);

        /*Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('KIT');
            $table->dropColumn('PRODUCT_SIZE');
            $table->dropColumn('COLOR');
            $table->dropColumn('COLLECTION');
            $table->dropColumn('FABRIC');
            $table->dropColumn('STYLE');
            $table->dropColumn('HEIGHT');
            $table->dropColumn('OUTLET_REF');
            $table->dropColumn('SALE_REF');
        });*/
    }
}
