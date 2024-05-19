<?php

use App\Enums\Promo\CartSortForSale;
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
        Schema::table('sales', function (Blueprint $table) {
            $table->tinyInteger('cart_sort')
                ->default(CartSortForSale::PRICE_ASC)
                ->after('algorithm')
                ->comment('Sorting the cart before applying the discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('cart_sort');
        });
    }
};
