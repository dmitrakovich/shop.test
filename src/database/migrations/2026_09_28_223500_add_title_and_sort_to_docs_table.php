<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Titles and menu order from the old admin documentation section.
     * Sort 80 is reserved for the "Редактировать" item in the new admin.
     *
     * @var array<string, array{0: string, 1: int}>
     */
    private array $pages = [
        'manager_instagram' => ['Менеджер - Instagram', 10],
        'manager_ordercheckout' => ['Оформление заказа', 20],
        'manager_manual_addproduct' => ['Менеджер - Заполнение товара', 30],
        'manager_script' => ['Менеджер - Скрипты', 40],
        'manager_utm' => ['Менеджер - Ссылки', 50],
        'fotograph_manual' => ['Фотограф - Инструкции', 60],
        'manager_manual_orderstep' => ['Этапы заказа', 70],
        'test' => ['Админ - инструкции', 90],
        'rating' => ['Алгоритмы рейтинга — инструкция для менеджеров', 100],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('docs', function (Blueprint $table) {
            $table->string('title')->nullable()->after('slug');
            $table->unsignedSmallInteger('sort')->default(0)->after('title');
        });

        foreach ($this->pages as $slug => [$title, $sort]) {
            DB::table('docs')->where('slug', $slug)->update([
                'title' => $title,
                'sort' => $sort,
            ]);
        }

        $nextSort = 110;

        foreach (DB::table('docs')->whereNull('title')->orderBy('id')->get() as $doc) {
            DB::table('docs')->where('id', $doc->id)->update([
                'title' => $doc->slug,
                'sort' => $nextSort,
            ]);
            $nextSort += 10;
        }

        Schema::table('docs', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('docs', function (Blueprint $table) {
            $table->dropColumn(['title', 'sort']);
        });
    }
};
