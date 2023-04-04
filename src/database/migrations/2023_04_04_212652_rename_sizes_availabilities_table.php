<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('sizes_availabilities', 'available_sizes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('available_sizes', 'sizes_availabilities');
    }
};
