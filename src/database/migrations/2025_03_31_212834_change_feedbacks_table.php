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
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropColumn('yandex_id');
            $table->dropColumn('user_email');
            $table->dropColumn('user_phone');
            $table->dropColumn('view_only_posted');
            $table->renameColumn('type_id', 'type');
            $table->foreignId('device_id')->nullable()->after('user_id')->index();
        });

        Schema::table('feedback_answers', function (Blueprint $table) {
            $table->after('feedback_id', fn (Blueprint $table) => $table->morphs('user'));
            $table->dropColumn('admin_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->unsignedBigInteger('yandex_id')->nullable()->after('user_id')->index();
            $table->string('user_email')->nullable()->after('user_name');
            $table->unsignedBigInteger('user_phone')->nullable()->after('user_email');
            $table->boolean('view_only_posted')->default(true)->after('captcha_score');
            $table->renameColumn('type', 'type_id');
            $table->dropColumn('device_id');
        });

        Schema::table('feedback_answers', function (Blueprint $table) {
            $table->dropMorphs('user');
            $table->foreignId('admin_id')->nullable()->after('feedback_id')->index();
        });
    }
};
