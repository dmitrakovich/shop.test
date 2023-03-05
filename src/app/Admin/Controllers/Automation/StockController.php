<?php

namespace App\Admin\Controllers\Automation;

use App\Models\Product;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Show;

class StockController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Product';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product());

        $grid->column('id', __('Id'));
        $grid->column('slug', __('Slug'));
        $grid->column('sku', __('Sku'));
        $grid->column('label_id', __('Label id'));
        $grid->column('buy_price', __('Buy price'));
        $grid->column('price', __('Price'));
        $grid->column('old_price', __('Old price'));
        $grid->column('category_id', __('Category id'));
        $grid->column('season_id', __('Season id'));
        $grid->column('brand_id', __('Brand id'));
        $grid->column('manufacturer_id', __('Manufacturer id'));
        $grid->column('collection_id', __('Collection id'));
        $grid->column('color_txt', __('Color txt'));
        $grid->column('fabric_top_txt', __('Fabric top txt'));
        $grid->column('fabric_inner_txt', __('Fabric inner txt'));
        $grid->column('fabric_insole_txt', __('Fabric insole txt'));
        $grid->column('fabric_outsole_txt', __('Fabric outsole txt'));
        $grid->column('heel_txt', __('Heel txt'));
        $grid->column('bootleg_height_txt', __('Bootleg height txt'));
        $grid->column('description', __('Description'));
        $grid->column('action', __('Action'));
        $grid->column('rating', __('Rating'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('deleted_at', __('Deleted at'));
        $grid->column('product_group_id', __('Product group id'));

        $grid->filter(function (Filter $filter) {
            $filter->in('brand_id', 'Бренд');
            // $filter->like('last_name', 'Фамилия');
            // $filter->like('patronymic_name', 'Отчество');
            // $filter->like('phone', 'Телефон');
            // $filter->equal('group_id', 'Группа')->select(Group::query()->pluck('name', 'id'));
            // $filter->like('email', 'Email');
            // $filter->like('addresses.city', 'Город');
            // $filter->like('addresses.address', 'Адрес');
        });

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
        $show = new Show(Product::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('slug', __('Slug'));
        $show->field('sku', __('Sku'));
        $show->field('label_id', __('Label id'));
        $show->field('buy_price', __('Buy price'));
        $show->field('price', __('Price'));
        $show->field('old_price', __('Old price'));
        $show->field('category_id', __('Category id'));
        $show->field('season_id', __('Season id'));
        $show->field('brand_id', __('Brand id'));
        $show->field('manufacturer_id', __('Manufacturer id'));
        $show->field('collection_id', __('Collection id'));
        $show->field('color_txt', __('Color txt'));
        $show->field('fabric_top_txt', __('Fabric top txt'));
        $show->field('fabric_inner_txt', __('Fabric inner txt'));
        $show->field('fabric_insole_txt', __('Fabric insole txt'));
        $show->field('fabric_outsole_txt', __('Fabric outsole txt'));
        $show->field('heel_txt', __('Heel txt'));
        $show->field('bootleg_height_txt', __('Bootleg height txt'));
        $show->field('description', __('Description'));
        $show->field('action', __('Action'));
        $show->field('rating', __('Rating'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('deleted_at', __('Deleted at'));
        $show->field('product_group_id', __('Product group id'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product());

        $form->text('slug', __('Slug'));
        $form->text('sku', __('Sku'));
        $form->number('label_id', __('Label id'));
        $form->decimal('buy_price', __('Buy price'))->default(0.00);
        $form->decimal('price', __('Price'))->default(0.00);
        $form->decimal('old_price', __('Old price'))->default(0.00);
        $form->number('category_id', __('Category id'));
        $form->number('season_id', __('Season id'));
        $form->number('brand_id', __('Brand id'));
        $form->number('manufacturer_id', __('Manufacturer id'));
        $form->number('collection_id', __('Collection id'));
        $form->text('color_txt', __('Color txt'));
        $form->text('fabric_top_txt', __('Fabric top txt'));
        $form->text('fabric_inner_txt', __('Fabric inner txt'));
        $form->text('fabric_insole_txt', __('Fabric insole txt'));
        $form->text('fabric_outsole_txt', __('Fabric outsole txt'));
        $form->text('heel_txt', __('Heel txt'));
        $form->text('bootleg_height_txt', __('Bootleg height txt'));
        $form->textarea('description', __('Description'));
        $form->switch('action', __('Action'));
        $form->number('rating', __('Rating'));
        $form->number('product_group_id', __('Product group id'));

        return $form;
    }
}
