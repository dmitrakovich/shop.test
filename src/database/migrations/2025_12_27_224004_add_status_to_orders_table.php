<?php

use App\Enums\Order\OrderStatus;
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
        Schema::table('orders', function (Blueprint $table) {
            $table->tinyInteger('status')
                ->after('status_key')
                ->default(OrderStatus::NEW)
                ->index();
        });

        DB::table('orders')->update([
            'status' => DB::raw("
                CASE status_key
                    WHEN 'new' THEN " . OrderStatus::NEW->value . "
                    WHEN 'canceled' THEN " . OrderStatus::CANCELED->value . "
                    WHEN 'in_work' THEN " . OrderStatus::IN_WORK->value . "
                    WHEN 'wait_payment' THEN " . OrderStatus::WAIT_PAYMENT->value . "
                    WHEN 'paid' THEN " . OrderStatus::PAID->value . "
                    WHEN 'assembled' THEN " . OrderStatus::ASSEMBLED->value . "
                    WHEN 'packaging' THEN " . OrderStatus::PACKAGING->value . "
                    WHEN 'ready' THEN " . OrderStatus::READY->value . "
                    WHEN 'sent' THEN " . OrderStatus::SENT->value . "
                    WHEN 'fitting' THEN " . OrderStatus::FITTING->value . "
                    WHEN 'complete' THEN " . OrderStatus::COMPLETED->value . "
                    WHEN 'return' THEN " . OrderStatus::RETURN->value . "
                    WHEN 'return_fitting' THEN " . OrderStatus::RETURN_FITTING->value . "
                    WHEN 'installment' THEN " . OrderStatus::INSTALLMENT->value . "
                    WHEN 'confirmed' THEN " . OrderStatus::CONFIRMED->value . "
                    WHEN 'partial_complete' THEN " . OrderStatus::PARTIAL_COMPLETED->value . "
                    WHEN 'delivered' THEN " . OrderStatus::DELIVERED->value . '
                    ELSE NULL
                END
            '),
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status_key')->nullable()->after('status');
        });

        DB::table('orders')->update([
            'status_key' => DB::raw('
                CASE status
                    WHEN ' . OrderStatus::NEW->value . " THEN 'new'
                    WHEN " . OrderStatus::CANCELED->value . " THEN 'canceled'
                    WHEN " . OrderStatus::IN_WORK->value . " THEN 'in_work'
                    WHEN " . OrderStatus::WAIT_PAYMENT->value . " THEN 'wait_payment'
                    WHEN " . OrderStatus::PAID->value . " THEN 'paid'
                    WHEN " . OrderStatus::ASSEMBLED->value . " THEN 'assembled'
                    WHEN " . OrderStatus::PACKAGING->value . " THEN 'packaging'
                    WHEN " . OrderStatus::READY->value . " THEN 'ready'
                    WHEN " . OrderStatus::SENT->value . " THEN 'sent'
                    WHEN " . OrderStatus::FITTING->value . " THEN 'fitting'
                    WHEN " . OrderStatus::COMPLETED->value . " THEN 'complete'
                    WHEN " . OrderStatus::RETURN->value . " THEN 'return'
                    WHEN " . OrderStatus::RETURN_FITTING->value . " THEN 'return_fitting'
                    WHEN " . OrderStatus::INSTALLMENT->value . " THEN 'installment'
                    WHEN " . OrderStatus::CONFIRMED->value . " THEN 'confirmed'
                    WHEN " . OrderStatus::PARTIAL_COMPLETED->value . " THEN 'partial_complete'
                    WHEN " . OrderStatus::DELIVERED->value . " THEN 'delivered'
                    ELSE NULL
                END
            "),
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
