<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * List of sizes
     */
    const SIZES = [31, 32, 42, 43, 44, 45, 46, 47, 48];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (self::SIZES as $size) {
            DB::table('sizes')->insert([
                'name' => $size,
                'slug' => 'size-' . $size,
                'value' => $size,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $slugs = array_map(fn (int $size) => 'size-' . $size, self::SIZES);

        DB::table('sizes')->whereIn('slug', $slugs)->delete();
    }
};
