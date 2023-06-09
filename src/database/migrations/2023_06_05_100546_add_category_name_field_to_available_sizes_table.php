<?php

use App\Models\Config;
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
        Schema::table('available_sizes', function (Blueprint $table) {
            $table->string('category_name')->nullable()->after('sku');
        });

        Config::create([
            'key' => 'inventory_blacklist',
            'config' => [
                'categories' => [
                    'Professional Anticolor',
                    'Водоотталк. пропитка',
                    'Восстановитель цвета для замши и нубука нейтральный',
                    'Восстановитель цвета для замши и нубука черный',
                    'Гелевые запяточники',
                    'Гелевые подпяточники',
                    'Гелевые полустельки',
                    'Дезодорант',
                    'Кожаные запяточники',
                    'Кожаные подпяточники',
                    'Кожаные полустельки',
                    'Краска-аэрозоль',
                    'Краска-восстановитель цвета',
                    'Крем',
                ],
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('available_sizes', function (Blueprint $table) {
            $table->dropColumn('category_name');
        });
    }
};
