<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('banners', function (Blueprint $table) {
            DB::statement("ALTER TABLE banners CHANGE position position ENUM('catalog_top', 'index_main', 'index_top', 'index_bottom', 'main_menu_catalog', 'catalog_mob', 'feedback', 'feedback_mob')");
            //
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('banners', function (Blueprint $table) {
            DB::statement("ALTER TABLE banners CHANGE position position ENUM('catalog_top', 'index_main', 'index_top', 'index_bottom', 'main_menu_catalog', 'catalog_mob')");
            //
        });
    }
};
