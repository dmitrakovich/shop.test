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
        $transitionalDefinition = '\'catalog_main\',\'catalog_top\',\'catalog_mob\',\'index_main\',\'index_top\',\'index_bottom\',\'main_menu_catalog\',\'feedback\',\'feedback_mob\'';
        $finalDefinition = '\'catalog_main\',\'index_main\',\'index_top\',\'index_bottom\',\'main_menu_catalog\',\'feedback\',\'feedback_mob\'';

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($transitionalDefinition)
        );

        DB::table('banners')
            ->whereIn('position', ['catalog_top', 'catalog_mob'])
            ->update(['position' => 'catalog_main']);

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($finalDefinition)
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $transitionalDefinition = '\'catalog_main\',\'catalog_top\',\'catalog_mob\',\'index_main\',\'index_top\',\'index_bottom\',\'main_menu_catalog\',\'feedback\',\'feedback_mob\'';
        $originalDefinition = '\'catalog_top\',\'index_main\',\'index_top\',\'index_bottom\',\'main_menu_catalog\',\'catalog_mob\',\'feedback\',\'feedback_mob\'';

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($transitionalDefinition)
        );

        DB::table('banners')->where('position', 'catalog_main')->update(['position' => 'catalog_top']);

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($originalDefinition)
        );
    }
};
