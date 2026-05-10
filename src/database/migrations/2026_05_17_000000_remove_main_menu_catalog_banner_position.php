<?php

use App\Enums\MorphMap;
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
        $ids = DB::table('banners')
            ->where('position', 'main_menu_catalog')
            ->pluck('id');

        if ($ids->isNotEmpty()) {
            DB::table('media')
                ->where('model_type', MorphMap::Banner->value)
                ->whereIn('model_id', $ids->all())
                ->delete();

            DB::table('banners')->whereIn('id', $ids->all())->delete();
        }

        $finalDefinition = '\'catalog_main\',\'feedback_main\',\'index_main\',\'index_double\',\'index_category\'';

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($finalDefinition)
        );
    }

    /**
     * Reverse the migrations.
     *
     * Deleted banner rows are not restored.
     */
    public function down(): void
    {
        $withLegacy = '\'catalog_main\',\'feedback_main\',\'index_main\',\'index_double\',\'index_category\',\'main_menu_catalog\'';

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql($withLegacy)
        );
    }
};
