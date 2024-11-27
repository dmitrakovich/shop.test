<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('discount_card_number', 9)->nullable()->comment('relation with 1C user')->change();
        });

        DB::table('users')
            ->whereNotNull('discount_card_number')
            ->update([
                'discount_card_number' => DB::raw("LPAD(discount_card_number, 9, ' ')"),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('discount_card_number', 10)->nullable()->comment('relation with 1C user')->change();
        });

        DB::table('users')
            ->whereNotNull('discount_card_number')
            ->update([
                'discount_card_number' => DB::raw('TRIM(discount_card_number)'),
            ]);
    }
};
