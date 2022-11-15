<?php

namespace App\Admin\Controllers\Forms;

use App\Models\Ads\ProductCarousel;
use App\Models\Category;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;

class ImidjSlider extends Form
{
    /**
     * The form title.
     *
     * @var string
     */
    public $title = 'Имиджевый';

    /**
     * The description of form.
     *
     * @var string
     */
    public $description = 'слайдер';

    /**
     * Handle the form request.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request)
    {
        $imidjSlider = ProductCarousel::where('is_imidj', true)->firstOrNew();

        $imidjSlider->is_imidj = true;
        $imidjSlider->title = $request->input('title', null);
        $imidjSlider->categories_list = $request->input('categories_list');
        $imidjSlider->speed = $request->input('speed', 3000);
        $imidjSlider->count = 24;
        $imidjSlider->save();

        // dd($imidjSlider, $request->all());

        admin_success('Имиджевый слайдер успешно сохранен!');

        return back();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('title', 'Заголовок');
        $this->multipleSelect('categories_list', 'Категории')->options(Category::getFormatedTree())->required();
        $this->number('speed', 'Скорость (мс)')->default(3000);
        // $this->number('count', 'Количество выводимых товаров')->default(24);
    }

    /**
     * The data of the form.
     *
     * @return array $data
     */
    public function data()
    {
        return optional(ProductCarousel::where('is_imidj', true)
            ->first(['title', 'categories', 'speed']))
            ->toArray();
    }
}
