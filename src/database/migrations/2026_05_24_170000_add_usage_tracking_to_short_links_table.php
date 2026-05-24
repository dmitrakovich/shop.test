<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('short_links', function (Blueprint $table) {
            $table->unsignedInteger('hits_count')->default(0)->after('full_link');
            $table->timestamp('last_used_at')->nullable()->after('hits_count');
        });
    }

    public function down(): void
    {
        Schema::table('short_links', function (Blueprint $table) {
            $table->dropColumn(['hits_count', 'last_used_at']);
        });
    }
};
