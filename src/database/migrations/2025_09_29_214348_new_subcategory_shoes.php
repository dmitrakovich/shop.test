<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Category::query()->doesntExist()) {
            return;
        }

        Category::query()
            ->where('id', Category::ROOT_CATEGORY_ID)
            ->update(['one_c_name' => null]);

        $shoes = Category::query()->create([
            'slug' => 'shoes',
            'title' => 'Женская обувь',
            'one_c_name' => 'Обувь женская',
            'parent_id' => Category::ROOT_CATEGORY_ID,
            'order' => 1,
            'path' => 'catalog/shoes',
        ]);

        $shoes->url()->create(['slug' => $shoes->slug]);

        $rootCategory = Category::query()->find(Category::ROOT_CATEGORY_ID);
        $shoes->moveAfter($rootCategory);

        Category::withTrashed()
            ->whereNotIn('id', [1, 25, 26, 27, 28, $shoes->id])
            ->each(function (Category $category) use ($shoes) {
                $category->update([
                    'parent_id' => $category->parent_id === Category::ROOT_CATEGORY_ID ? $shoes->id : $category->parent_id,
                    'path' => str_replace('catalog', 'catalog/shoes', $category->path),
                ]);
            });

        Cache::forget('filters');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
