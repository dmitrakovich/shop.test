<?php

namespace App\Admin\Controllers\Forms;

use App\Enums\ProductCarouselEnum;
use App\Models\Ads\ProductCarousel;
use Illuminate\Http\Request;
use Encore\Admin\Widgets\Form;

class SimilarProductsSlider extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Похожие товары';

    /**
     * The description of form.
     *
     * @var string
     */
    public $description = 'слайдер';

    /**
     * Handle the form request.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $imidjSlider = ProductCarousel::where('enum_type_id', ProductCarouselEnum::SIMILAR_PRODUCTS)->firstOrNew();

        $imidjSlider->enum_type_id = ProductCarouselEnum::SIMILAR_PRODUCTS;
        $imidjSlider->title        = $request->input('title', null);
        $imidjSlider->speed        = $request->input('speed', 3000);
        $imidjSlider->count        = 12;
        $imidjSlider->save();

        admin_success('Слайдер похожие товары успешно сохранен!');
        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('title', 'Заголовок');
        $this->number('speed', 'Скорость (мс)')->default(3000);
        $this->number('count', 'Количество выводимых товаров')->default(12);
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return optional(ProductCarousel::where('enum_type_id', ProductCarouselEnum::SIMILAR_PRODUCTS)
            ->first(['title', 'speed', 'count']))
            ->toArray();
    }
}
