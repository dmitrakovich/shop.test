<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->unsignedBigInteger('belpost_list_id')->nullable()->after('dispatch_date');
            $table->string('belpost_status', 32)->nullable()->after('belpost_list_id');
            $table->string('name')->nullable()->after('belpost_status');
            $table->string('postal_delivery_type', 64)->nullable()->after('name');
            $table->string('direction', 16)->default('internal')->after('postal_delivery_type');
            $table->string('payment_type', 64)->nullable()->after('direction');
            $table->boolean('negotiated_rate')->default(false)->after('payment_type');
            $table->boolean('is_declared_value')->default(false)->after('negotiated_rate');
            $table->boolean('is_partial_receipt')->default(false)->after('is_declared_value');
            $table->unsignedBigInteger('belpost_document_id')->nullable()->after('is_partial_receipt');
            $table->text('belpost_sync_error')->nullable()->after('belpost_document_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('belpost_item_id')->nullable()->after('batch_id');
            $table->string('belpost_s10code', 32)->nullable()->after('belpost_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('batches', function (Blueprint $table) {
            $table->dropColumn([
                'belpost_list_id',
                'belpost_status',
                'name',
                'postal_delivery_type',
                'direction',
                'payment_type',
                'negotiated_rate',
                'is_declared_value',
                'is_partial_receipt',
                'belpost_document_id',
                'belpost_sync_error',
            ]);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['belpost_item_id', 'belpost_s10code']);
        });
    }
};
