<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\Category;
use App\Models\Ads\ProductCarousel;
use Encore\Admin\Controllers\AdminController;

class ProductCarouselController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'ProductCarousel';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ProductCarousel());

        $grid->sortable();

        $grid->column('title', 'Заголовок');
        $grid->column('category.title', 'Категория');
        $grid->column('only_sale', 'Только со скидкой')->switch();
        $grid->column('only_new', 'Только новинки')->switch();
        $grid->column('count', 'Количество товаров');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        return back();
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ProductCarousel());

        $form->text('title', 'Заголовок');
        $form->select('category_id', 'Категория')->options(Category::getFormatedTree())->required();
        $form->switch('only_sale', 'Только товары со скидкой');
        $form->switch('only_new', 'Только новинки');
        $form->number('count', 'Количество выводимых товаров')->default(15);

        return $form;
    }
}
