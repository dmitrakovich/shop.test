<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Before running: find and resolve duplicate (sku, brand_id) pairs, e.g.
 *
 * SELECT sku, brand_id, COUNT(*) AS cnt, GROUP_CONCAT(id ORDER BY id) AS product_ids
 * FROM products
 * GROUP BY sku, brand_id
 * HAVING COUNT(*) > 1;
 *
 * SELECT p.id, p.sku, p.brand_id, p.deleted_at, p.slug, p.created_at
 * FROM products p
 * INNER JOIN (
 *     SELECT sku, brand_id
 *     FROM products
 *     GROUP BY sku, brand_id
 *     HAVING COUNT(*) > 1
 * ) dup ON dup.sku = p.sku AND dup.brand_id = p.brand_id
 * ORDER BY p.sku, p.brand_id, p.id;
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unique(['sku', 'brand_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku', 'brand_id']);
        });
    }
};
