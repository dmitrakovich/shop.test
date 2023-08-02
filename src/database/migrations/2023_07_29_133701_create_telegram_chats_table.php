<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('telegram_chats', function (Blueprint $table) {
            $table->id();
            $table->string('chat_id');
            $table->string('name')->nullable();

            $table->foreignId('telegram_bot_id')->constrained('telegram_bots')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['chat_id', 'telegram_bot_id']);
        });
    }
};
