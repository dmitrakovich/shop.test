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
        Schema::create('seo_links', function (Blueprint $table) {
            $table->id();

            $table->tinyInteger('folder_enum_id')->unsigned()->nullable()->comment('Enum id каталога');
            $table->string('seo_url', 255)->nullable()->comment('Seo ссылка');
            $table->string('destination', 255)->nullable()->comment('Куда приведет seo ссылка');
            $table->string('tag', 255)->nullable()->comment('Тег');
            $table->integer('frequency')->nullable()->comment('Частота');
            $table->dateTime('frequency_updated_at')->nullable()->comment('Дата/время обновления поля частота');
            $table->string('h1', 255)->nullable()->comment('h1 заголовок');
            $table->longText('main_text')->nullable()->comment('Основной текст');
            $table->string('meta_title', 255)->nullable()->comment('Meta title');
            $table->text('meta_description')->nullable()->comment('Meta description');
            $table->text('meta_keywords')->nullable()->comment('Meta keywords');

            $table->index('destination');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seo_links');
    }
};
