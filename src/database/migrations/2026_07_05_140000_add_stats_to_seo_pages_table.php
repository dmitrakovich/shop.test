<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_pages', function (Blueprint $table): void {
            $table->unsignedInteger('pageviews')->default(0)->after('tag_name');
            $table->unsignedInteger('visits')->default(0)->after('pageviews');
            $table->double('score')->default(0)->after('visits');
        });
    }

    public function down(): void
    {
        Schema::table('seo_pages', function (Blueprint $table): void {
            $table->dropColumn(['pageviews', 'visits', 'score']);
        });
    }
};
