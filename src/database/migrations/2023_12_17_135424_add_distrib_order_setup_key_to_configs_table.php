<?php

use App\Models\Config;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('configs', function (Blueprint $table) {
            Config::create([
                'key' => 'distrib_order_setup',
                'config' => [
                    'active' => false,
                    'schedule' => [],
                ],
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configs', function (Blueprint $table) {
            Config::where('key', 'distrib_order_setup')->delete();
        });
    }
};
