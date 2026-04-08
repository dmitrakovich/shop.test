<?php

use App\Enums\Ads\BannerType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table
                ->string('desktop_type')
                ->default(BannerType::IMAGE)
                ->after('type')
                ->comment('Тип десктоп баннера');

            $table
                ->string('mobile_type')
                ->default(BannerType::IMAGE)
                ->after('desktop_type')
                ->comment('Тип мобильного баннера');
        });

        DB::table('banners')->update([
            'desktop_type' => DB::raw('type'),
            'mobile_type' => DB::raw('type'),
        ]);

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('type');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table
                ->string('type')
                ->default('image')
                ->after('mobile_type')
                ->comment('Тип баннера');
        });

        DB::table('banners')->update([
            'type' => DB::raw('desktop_type'),
        ]);

        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['desktop_type', 'mobile_type']);
        });
    }
};
