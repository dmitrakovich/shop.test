<?php

use App\Enums\Order\OrderItemStatus;
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
        Schema::table('order_items', function (Blueprint $table) {
            $table->tinyInteger('status')
                ->after('status_key')
                ->default(OrderItemStatus::NEW)
                ->index();
        });

        DB::table('order_items')->update([
            'status' => DB::raw("
                CASE status_key
                    WHEN 'new' THEN " . OrderItemStatus::NEW->value . "
                    WHEN 'reserved' THEN " . OrderItemStatus::RESERVED->value . "
                    WHEN 'pickup' THEN " . OrderItemStatus::PICKUP->value . "
                    WHEN 'packaging' THEN " . OrderItemStatus::PACKAGING->value . "
                    WHEN 'sent' THEN " . OrderItemStatus::SENT->value . "
                    WHEN 'fitting' THEN " . OrderItemStatus::FITTING->value . "
                    WHEN 'complete' THEN " . OrderItemStatus::COMPLETED->value . "
                    WHEN 'return' THEN " . OrderItemStatus::RETURN->value . "
                    WHEN 'canceled' THEN " . OrderItemStatus::CANCELED->value . "
                    WHEN 'return_fitting' THEN " . OrderItemStatus::RETURN_FITTING->value . "
                    WHEN 'no_availability' THEN " . OrderItemStatus::NO_AVAILABILITY->value . "
                    WHEN 'installment' THEN " . OrderItemStatus::INSTALLMENT->value . "
                    WHEN 'confirmed' THEN " . OrderItemStatus::CONFIRMED->value . "
                    WHEN 'collect' THEN " . OrderItemStatus::COLLECT->value . "
                    WHEN 'waiting_refund' THEN " . OrderItemStatus::WAITING_REFUND->value . "
                    WHEN 'displacement' THEN " . OrderItemStatus::DISPLACEMENT->value . '
                    ELSE NULL
                END
            '),
        ]);

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('status_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('status_key')->nullable()->after('status');
        });

        DB::table('order_items')->update([
            'status_key' => DB::raw('
                CASE status
                    WHEN ' . OrderItemStatus::NEW->value . " THEN 'new'
                    WHEN " . OrderItemStatus::RESERVED->value . " THEN 'reserved'
                    WHEN " . OrderItemStatus::PICKUP->value . " THEN 'pickup'
                    WHEN " . OrderItemStatus::PACKAGING->value . " THEN 'packaging'
                    WHEN " . OrderItemStatus::SENT->value . " THEN 'sent'
                    WHEN " . OrderItemStatus::FITTING->value . " THEN 'fitting'
                    WHEN " . OrderItemStatus::COMPLETED->value . " THEN 'complete'
                    WHEN " . OrderItemStatus::RETURN->value . " THEN 'return'
                    WHEN " . OrderItemStatus::CANCELED->value . " THEN 'canceled'
                    WHEN " . OrderItemStatus::RETURN_FITTING->value . " THEN 'return_fitting'
                    WHEN " . OrderItemStatus::NO_AVAILABILITY->value . " THEN 'no_availability'
                    WHEN " . OrderItemStatus::INSTALLMENT->value . " THEN 'installment'
                    WHEN " . OrderItemStatus::CONFIRMED->value . " THEN 'confirmed'
                    WHEN " . OrderItemStatus::COLLECT->value . " THEN 'collect'
                    WHEN " . OrderItemStatus::WAITING_REFUND->value . " THEN 'waiting_refund'
                    WHEN " . OrderItemStatus::DISPLACEMENT->value . " THEN 'displacement'
                    ELSE NULL
                END
            "),
        ]);

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
