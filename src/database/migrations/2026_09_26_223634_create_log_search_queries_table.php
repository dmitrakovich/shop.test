<?php

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
        Schema::create('log_search_queries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete()->cascadeOnUpdate();
            $table->string('query', 255);
            $table->unsignedInteger('results_count');
            $table->string('filters_path', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            // Covers the top-queries report: 30-day range, results_count filter, group by query, distinct devices.
            $table->index(['created_at', 'results_count', 'query', 'device_id'], 'log_search_queries_top_queries_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_search_queries');
    }
};
