<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_pages', function (Blueprint $table): void {
            $table->id();
            $table->string('page_type', 32)->default('catalog');
            $table->string('url', 512);
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('h1', 255)->nullable();
            $table->string('seo_text_title', 255)->nullable();
            $table->longText('seo_text')->nullable();
            $table->text('keywords')->nullable();
            $table->string('tag_name', 255)->nullable();
            $table->timestamps();

            $table->unique('url');
            $table->index('page_type');
        });

        $this->migrateFromSeoLinks();
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_pages');
    }

    private function migrateFromSeoLinks(): void
    {
        if (!Schema::hasTable('seo_links')) {
            return;
        }

        $rows = DB::table('seo_links')
            ->whereNotNull('destination')
            ->where('destination', '!=', '')
            ->orderBy('id')
            ->get();

        $now = now();

        foreach ($rows as $row) {
            DB::table('seo_pages')->insertOrIgnore([
                'page_type' => 'catalog',
                'url' => $row->destination,
                'title' => $row->meta_title,
                'description' => $row->meta_description,
                'h1' => $row->h1,
                'seo_text_title' => null,
                'seo_text' => $row->main_text,
                'keywords' => $row->meta_keywords,
                'tag_name' => $row->tag,
                'created_at' => $row->created_at ?? $now,
                'updated_at' => $row->updated_at ?? $now,
            ]);
        }
    }
};
