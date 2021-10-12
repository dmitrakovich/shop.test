<?php

use App\Models\Feedback;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('yandex_id')->nullable()->index();
            $table->string('user_name');
            $table->string('user_email');
            $table->unsignedBigInteger('user_phone')->nullable();
            $table->text('text');
            $table->tinyInteger('rating')->default(5);
            $table->unsignedBigInteger('product_id')->index()->default(0);
            $table->tinyInteger('type_id')->default(Feedback::TYPE_REVIEW);
            $table->tinyInteger('captcha_score')->default(0);
            $table->boolean('view_only_posted')->default(true);
            $table->boolean('publish')->default(true);
            $table->ipAddress('ip');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feedbacks');
    }
}
