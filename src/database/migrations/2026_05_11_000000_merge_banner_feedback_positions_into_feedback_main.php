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
        $transitionalDefinition = '\'feedback_main\',\'feedback\',\'feedback_mob\',\'catalog_main\',\'index_main\',\'index_top\',\'index_bottom\',\'main_menu_catalog\'';
        $finalDefinition = '\'catalog_main\',\'feedback_main\',\'index_main\',\'index_top\',\'index_bottom\',\'main_menu_catalog\'';

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($transitionalDefinition)
        );

        DB::table('banners')
            ->whereIn('position', ['feedback', 'feedback_mob'])
            ->update(['position' => 'feedback_main']);

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($finalDefinition)
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $transitionalDefinition = '\'feedback_main\',\'feedback\',\'feedback_mob\',\'catalog_main\',\'index_main\',\'index_top\',\'index_bottom\',\'main_menu_catalog\'';
        $originalDefinition = '\'catalog_main\',\'index_main\',\'index_top\',\'index_bottom\',\'main_menu_catalog\',\'feedback\',\'feedback_mob\'';

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($transitionalDefinition)
        );

        DB::table('banners')->where('position', 'feedback_main')->update(['position' => 'feedback']);

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($originalDefinition)
        );
    }
};
