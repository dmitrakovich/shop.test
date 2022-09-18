<?php

namespace App\Admin\Controllers\Forms;

use App\Enums\ProductCarouselEnum;
use App\Models\Ads\ProductCarousel;
use Illuminate\Http\Request;
use Encore\Admin\Widgets\Form;

class RecentProductsSlider extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Недавно просмотренные товары';

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
        $slider = ProductCarousel::where('enum_type_id', ProductCarouselEnum::RECENT_PRODUCTS)->firstOrNew();

        $slider->enum_type_id = ProductCarouselEnum::RECENT_PRODUCTS;
        $slider->title        = $request->input('title', null);
        $slider->speed        = $request->input('speed', 3000);
        $slider->count        = 12;
        $slider->save();

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
        return optional(ProductCarousel::where('enum_type_id', ProductCarouselEnum::RECENT_PRODUCTS)
            ->first(['title', 'speed', 'count']))
            ->toArray();
    }
}
