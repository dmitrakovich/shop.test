<?php

use App\Repositories\BannerRepository;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Legacy data used index_double vs index_category with reversed semantics vs the codebase.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->swapDoubleAndCategoryPositions();
        app(BannerRepository::class)->clearCache();
    }

    /**
     * Reverse the migrations (swap is self-inverse).
     */
    public function down(): void
    {
        $this->swapDoubleAndCategoryPositions();
        app(BannerRepository::class)->clearCache();
    }

    private function swapDoubleAndCategoryPositions(): void
    {
        DB::statement('UPDATE `banners` SET `position` = CASE `position`
                WHEN \'index_double\' THEN \'index_category\'
                WHEN \'index_category\' THEN \'index_double\'
                ELSE `position`
            END
            WHERE `position` IN (\'index_double\', \'index_category\')');
    }
};
