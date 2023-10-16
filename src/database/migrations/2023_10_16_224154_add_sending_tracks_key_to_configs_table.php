<?php

use App\Models\Ads\Mailing;
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
            Mailing::firstOrCreate([
                'name' => 'Отправка треков',
            ], [
                'description' => 'Отправка трека после заказа',
            ]);
            Config::create([
                'key' => 'sending_tracks',
                'config' => ['active' => false]
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configs', function (Blueprint $table) {
            Config::where('key', 'sending_tracks')->delete();
        });
    }
};
