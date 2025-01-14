<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->renameColumn('slug', 'old_slug');
            $table->string('slug', 50)->nullable()->after('one_c_id')->unique();
        });

        Product::withTrashed()
            ->with(['category'])
            ->each(function (Product $product) {
                $product->update(['slug' => Str::slug($product->shortName())]);
            });

        Schema::table('products', function (Blueprint $table) {
            $table->string('slug', 50)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->renameColumn('old_slug', 'slug');
            $table->unique(['slug']);
        });
    }
};
