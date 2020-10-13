<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    protected $tableName = 'products';
    protected $oldTableName = 'cyizj_jshopping_products';
    protected $oldCategoriesTable = 'cyizj_jshopping_products_to_categories';
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
        if (!Schema::hasTable($this->oldCategoriesTable)) {
            throw new Exception("Please, copy old product table '{$this->oldCategoriesTable}'");
        }

        Schema::dropIfExists($this->tableName);
        Schema::rename($this->oldTableName, $this->tableName);

        Schema::table($this->tableName, function (Blueprint $table) {
            $table->datetime('product_date_added')->default(NULL)->change();
            $table->datetime('date_modify')->default(NULL)->change();

            $table->renameColumn('product_id', 'id');

            $table->uuid('slug')->unique()->nullable();
            $table->foreignId('category_id')->index();
            $table->foreignId('season_id')->index();
            $table->foreignId('color_id')->index();
            $table->foreignId('brand_id')->index();
            $table->timestamps();
            $table->softDeletes();
        });
        
        // дата создания и обновления
        DB::update("UPDATE {$this->tableName} SET created_at = product_date_added, updated_at = date_modify
            WHERE product_date_added <> '0000-00-00 00:00:00' AND date_modify  <> '0000-00-00 00:00:00'");

        // категория
        DB::update("UPDATE products p, {$this->oldCategoriesTable} c SET p.category_id=c.category_id WHERE p.id=c.product_id");
        // сезон
        DB::update("UPDATE products SET season_id=extra_field_7 WHERE extra_field_7 <> ''");
        // производитель
        DB::update("UPDATE products SET brand_id=product_manufacturer_id");

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
            $table->dropColumn('image');

            $table->dropColumn('name_en-GB');
            $table->dropColumn('alias_en-GB');
            $table->dropColumn('short_description_en-GB');
            $table->dropColumn('description_en-GB');
            $table->dropColumn('meta_title_en-GB');
            $table->dropColumn('meta_description_en-GB');
            $table->dropColumn('meta_keyword_en-GB');
        });

        Schema::table($this->tableName, function (Blueprint $table) {
            $table->renameColumn('extra_field_1', 'color_txt');
            $table->renameColumn('extra_field_2', 'fabric_top_txt');
            $table->renameColumn('extra_field_8', 'fabric_inner_txt');
            $table->renameColumn('extra_field_9', 'fabric_insole_txt');
            $table->renameColumn('extra_field_10', 'fabric_outsole_txt');
            $table->renameColumn('extra_field_11', 'heel_txt');
        });

        $this->call(ProductAttributeSeeder::class);

        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropColumn('extra_field_3'); // Коллекция
            $table->dropColumn('extra_field_7'); // Сезон
            $table->dropColumn('extra_field_12'); // Размер аксессуара
            $table->dropColumn('extra_field_13'); // Цвет фильтра
            $table->dropColumn('extra_field_14'); // Материал фильтра
            $table->dropColumn('extra_field_15'); // Теги
            $table->dropColumn('extra_field_16'); // Акция
            $table->dropColumn('extra_field_17'); // Поднять
            $table->dropColumn('extra_field_18'); // Рейтинг
        });
    }
}
