<?php

use App\Enums\Ads\BannerPosition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function positionEnumSql(string $definition, bool $nullable): string
    {
        $nullability = $nullable ? 'DEFAULT NULL' : "NOT NULL DEFAULT '" . BannerPosition::INDEX_MAIN->value . "'";

        return "ENUM({$definition}) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci {$nullability}";
    }

    /**
     * @return non-empty-string
     */
    private function positionEnumDefinition(): string
    {
        return '\'catalog_main\',\'feedback_main\',\'index_main\',\'index_double\',\'index_category\'';
    }

    public function up(): void
    {
        DB::table('banners')
            ->whereNull('position')
            ->update(['position' => BannerPosition::INDEX_MAIN->value]);

        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql(
                $this->positionEnumDefinition(),
                false,
            )
        );
    }

    public function down(): void
    {
        DB::statement(
            'ALTER TABLE `banners` MODIFY COLUMN `position` ' . $this->positionEnumSql(
                $this->positionEnumDefinition(),
                true,
            )
        );
    }
};
