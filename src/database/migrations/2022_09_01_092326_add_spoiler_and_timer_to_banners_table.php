<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
          DB::statement("ALTER TABLE banners CHANGE position position ENUM('catalog_top', 'index_main', 'index_top', 'index_bottom', 'main_menu_catalog', 'catalog_mob')");
          $table->boolean('show_timer')->nullable();
          $table->dateTime('timer')->nullable();
          $table->json('spoiler')->nullable();
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
          DB::statement("ALTER TABLE banners CHANGE position position ENUM('catalog_top', 'index_main', 'index_top', 'index_bottom', 'main_menu_catalog')");
          $table->dropColumn('show_timer');
          $table->dropColumn('timer');
          $table->dropColumn('spoiler');
        });
    }
};
