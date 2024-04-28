<?php

use App\Enums\Promo\SaleAlgorithm;
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
        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('sale', 'sale_percentage');
            $table->renameColumn('algorithm', 'algorithm_old');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedTinyInteger('algorithm')->default(SaleAlgorithm::SIMPLE)->after('algorithm_old');
            $table->float('sale_percentage')->nullable()->comment('discount amount in percentage')->change();
            $table->unsignedInteger('sale_fix')->nullable()->after('sale_percentage')->comment('fixed discount amount');
        });

        DB::table('sales')->where('algorithm_old', 'simle')->update(['algorithm' => SaleAlgorithm::SIMPLE]);
        DB::table('sales')->where('algorithm_old', 'count')->update(['algorithm' => SaleAlgorithm::COUNT]);
        DB::table('sales')->where('algorithm_old', 'fake')->update(['algorithm' => SaleAlgorithm::FAKE]);
        DB::table('sales')->where('algorithm_old', 'ascending')->update(['algorithm' => SaleAlgorithm::ASCENDING]);

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('algorithm_old');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('algorithm_old')->after('sale_fix')->nullable();
        });

        DB::table('sales')->where('algorithm', SaleAlgorithm::SIMPLE)->update(['algorithm_old' => 'simple']);
        DB::table('sales')->where('algorithm', SaleAlgorithm::COUNT)->update(['algorithm_old' => 'count']);
        DB::table('sales')->where('algorithm', SaleAlgorithm::FAKE)->update(['algorithm_old' => 'fake']);
        DB::table('sales')->where('algorithm', SaleAlgorithm::ASCENDING)->update(['algorithm_old' => 'ascending']);

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('algorithm');
            $table->dropColumn('sale_fix');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->renameColumn('algorithm_old', 'algorithm');
            $table->renameColumn('sale_percentage', 'sale');
        });
    }
};
