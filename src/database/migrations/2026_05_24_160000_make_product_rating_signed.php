<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->modifyRatingColumn('INT');
    }

    public function down(): void
    {
        $this->modifyRatingColumn('INT UNSIGNED');
    }

    private function modifyRatingColumn(string $type): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            DB::statement("ALTER TABLE products MODIFY rating {$type} NOT NULL DEFAULT 0");
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
