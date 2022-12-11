<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('log_sms', function (Blueprint $table) {
            $table->index(['admin_id']);
            $table->index(['order_id']);
            $table->foreignId('user_id')->nullable()->after('admin_id')->index();
            $table->foreignId('mailing_id')->nullable()->after('order_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mailings');

        Schema::table('log_sms', function (Blueprint $table) {
            $table->dropIndex(['admin_id']);
            $table->dropIndex(['order_id']);
            $table->dropColumn('user_id');
            $table->dropColumn('mailing_id');
        });
    }
};
