<?php

namespace App\Admin\Controllers\Forms;

use App\Enums\ProductCarouselEnum;
use App\Models\Ads\ProductCarousel;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class ProductGroupSlider extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Группа товаров';

    /**
     * The description of form.
     *
     * @var string
     */
    public $description = 'слайдер';

    /**
     * Handle the form request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $slider = ProductCarousel::where('enum_type_id', ProductCarouselEnum::PRODUCT_GROUP)->firstOrNew();

        $slider->enum_type_id = ProductCarouselEnum::PRODUCT_GROUP;
        $slider->title = $request->input('title', null);
        $slider->speed = $request->input('speed', 3000);
        $slider->count = $request->input('count', 24);
        $slider->save();

        admin_success('Слайдер группы товаров успешно сохранен!');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('title', 'Заголовок');
        $this->number('speed', 'Скорость (мс)')->default(3000);
        $this->number('count', 'Количество выводимых товаров')->default(24);
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return optional(ProductCarousel::where('enum_type_id', ProductCarouselEnum::PRODUCT_GROUP)
            ->first(['title', 'speed', 'count']))
            ->toArray();
    }
}
