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
        Schema::create('sizes_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->index()->nullable();
            $table->unsignedInteger('one_c_product_id')->nullable();
            $table->foreignId('brand_id')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->foreignId('stock_id')->nullable();
            $table->string('sku');

            $table->float('buy_price');
            $table->float('sell_price');

            $table->unsignedInteger('size_none')->default(0);
            $table->unsignedInteger('size_31')->default(0);
            $table->unsignedInteger('size_32')->default(0);
            $table->unsignedInteger('size_33')->default(0);
            $table->unsignedInteger('size_34')->default(0);
            $table->unsignedInteger('size_35')->default(0);
            $table->unsignedInteger('size_36')->default(0);
            $table->unsignedInteger('size_37')->default(0);
            $table->unsignedInteger('size_38')->default(0);
            $table->unsignedInteger('size_39')->default(0);
            $table->unsignedInteger('size_40')->default(0);
            $table->unsignedInteger('size_41')->default(0);
            $table->unsignedInteger('size_42')->default(0);
            $table->unsignedInteger('size_43')->default(0);
            $table->unsignedInteger('size_44')->default(0);
            $table->unsignedInteger('size_45')->default(0);
            $table->unsignedInteger('size_46')->default(0);
            $table->unsignedInteger('size_47')->default(0);
            $table->unsignedInteger('size_48')->default(0);
        });

        Schema::table('brands', function (Blueprint $table) {
            $table->unsignedInteger('one_c_id')->unique()->after('id')->nullable();
            $table->dropColumn('logo');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('one_c_name')->unique()->after('title')->nullable();
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->unsignedInteger('one_c_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sizes_availabilities');

        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn('one_c_id');
            $table->string('logo')->default('default.png');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('one_c_name');
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->string('one_c_id', 20)->change();
        });
    }
};
