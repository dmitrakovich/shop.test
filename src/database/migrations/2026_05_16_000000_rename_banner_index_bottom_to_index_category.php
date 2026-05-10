<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function positionEnumSql(string $definition): string
    {
        return "ENUM({$definition}) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL";
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $transitionalDefinition = '\'index_category\',\'index_bottom\',\'catalog_main\',\'feedback_main\',\'index_main\',\'index_double\',\'main_menu_catalog\'';
        $finalDefinition = '\'catalog_main\',\'feedback_main\',\'index_main\',\'index_double\',\'index_category\',\'main_menu_catalog\'';

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($transitionalDefinition)
        );

        DB::table('banners')->where('position', 'index_bottom')->update(['position' => 'index_category']);

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($finalDefinition)
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $transitionalDefinition = '\'index_category\',\'index_bottom\',\'catalog_main\',\'feedback_main\',\'index_main\',\'index_double\',\'main_menu_catalog\'';
        $originalDefinition = '\'catalog_main\',\'feedback_main\',\'index_main\',\'index_double\',\'index_bottom\',\'main_menu_catalog\'';

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($transitionalDefinition)
        );

        DB::table('banners')->where('position', 'index_category')->update(['position' => 'index_bottom']);

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($originalDefinition)
        );
    }
};
