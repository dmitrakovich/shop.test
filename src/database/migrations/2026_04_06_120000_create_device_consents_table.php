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
        Schema::create('device_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->nullable()->constrained('devices')->nullOnDelete()->cascadeOnUpdate();
            $table->boolean('cookie_analytics_enabled')->nullable();
            $table->boolean('cookie_marketing_enabled')->nullable();
            $table->timestamp('personal_data_consent_recorded_at')->nullable();
            $table->boolean('personal_data_consent')->nullable();
            $table->unsignedTinyInteger('consent_request_source')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_consents');
    }
};
