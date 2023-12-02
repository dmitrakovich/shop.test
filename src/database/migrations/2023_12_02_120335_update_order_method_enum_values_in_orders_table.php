<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `order_method` ENUM('undefined','default','oneclick','chat','phone','email','viber','telegram','whatsapp','insta','other') NOT NULL DEFAULT 'default'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `orders` MODIFY COLUMN `order_method` ENUM('default','oneclick','chat','phone','email','viber','telegram','whatsapp','insta','other') NOT NULL DEFAULT 'default'");
    }
};
