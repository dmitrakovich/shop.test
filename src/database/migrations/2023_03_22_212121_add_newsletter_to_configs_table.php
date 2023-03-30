<?php

use App\Models\Config;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Config::create([
            'key' => 'newsletter_register',
            'config' => [
                'active' => true,
                'to_days' => 30,
                'from_days' => 5,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Config::where('key', 'newsletter_register')->delete();
    }
};
