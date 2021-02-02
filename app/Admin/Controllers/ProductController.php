<?php

namespace App\Admin\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Season;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProductController extends AdminController
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

        $grid->column('id', __('Id'))->sortable();


        $grid->column('media', __('Фото'))->display(function ($pictures) {
            /*$media = [];
            foreach ($this->getMedia() as $image) {
                $media[] = $image->getUrl('thumb');
            }
            return $media;*/
            return $this->getFirstMedia()->getUrl('thumb');
        })->image(); // ->carousel();




        $grid->column('publish', __('Publish'))->bool()->sortable();
        $grid->column('slug', __('Slug'));
        $grid->column('title', __('Title'));
        // $grid->column('buy_price', __('Buy price'));
        $grid->column('price', __('Price'))->sortable();
        $grid->column('old_price', __('Old price'))->sortable();
        // $grid->column('category_id', __('Category id'));
        $grid->column('category.title', __('Категория'));
        // $grid->column('season_id', __('Season id'));
        $grid->column('season.name', __('Сезон'));
        // $grid->column('color_id', __('Color id'));
        $grid->column('color.name', __('Цвет'));
        // $grid->column('brand_id', __('Brand id'));
        $grid->column('brand.name', __('Бренд'));
        $grid->column('color_txt', __('Color txt'));
        $grid->column('fabric_top_txt', __('Fabric top txt'));
        $grid->column('fabric_inner_txt', __('Fabric inner txt'));
        $grid->column('fabric_insole_txt', __('Fabric insole txt'));
        $grid->column('fabric_outsole_txt', __('Fabric outsole txt'));
        $grid->column('heel_txt', __('Heel txt'));
        // $grid->column('description', __('Description'));
        // $grid->column('created_at', __('Created at'));
        // $grid->column('updated_at', __('Updated at'));
        // $grid->column('deleted_at', __('Deleted at'));

        $grid->paginate(30);

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
        $show->field('publish', __('Publish'));
        $show->field('slug', __('Slug'));
        $show->field('title', __('Title'));
        $show->field('buy_price', __('Buy price'));
        $show->field('price', __('Price'));
        $show->field('old_price', __('Old price'));
        $show->field('category_id', __('Category id'));
        $show->field('season_id', __('Season id'));
        $show->field('color_id', __('Color id'));
        $show->field('brand_id', __('Brand id'));
        $show->field('color_txt', __('Color txt'));
        $show->field('fabric_top_txt', __('Fabric top txt'));
        $show->field('fabric_inner_txt', __('Fabric inner txt'));
        $show->field('fabric_insole_txt', __('Fabric insole txt'));
        $show->field('fabric_outsole_txt', __('Fabric outsole txt'));
        $show->field('heel_txt', __('Heel txt'));
        $show->field('description', __('Description'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('deleted_at', __('Deleted at'));

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

        $form->switch('publish', __('Publish'));
        $form->multipleImage('photos', __('Фотографии'))->removable()->downloadable();
        $form->text('slug', __('Slug'));
        $form->display('path', __('Path'));
        $form->text('title', __('Title'));
        // $form->decimal('buy_price', __('Buy price'));
        $form->decimal('price', 'Цена');
        $form->decimal('old_price', 'Старая цена');
        $form->select('category_id', 'Категория')->options(Category::getFormatedTree());
        $form->select('season_id', 'Сезон')->options(Season::all()->pluck('name','id'));
        $form->select('color_id', 'Цвет')->options(Color::all()->pluck('name','id'));
        $form->select('brand_id', 'Бренд')->options(Brand::all()->pluck('name','id'));
        $form->text('color_txt', 'Цвет');
        $form->text('fabric_top_txt', 'Материал верха');
        $form->text('fabric_inner_txt', 'Материал внутри');
        $form->text('fabric_insole_txt', 'Материал стельки');
        $form->text('fabric_outsole_txt', 'Материал подошвы');
        $form->text('heel_txt', 'Тип каблука/подошвы');
        $form->ckeditor('description', 'Описание');

        $form->saved(function (Form $form) {
            $form->model()->url()->updateOrCreate(['slug' => $form->slug]);
        });

        return $form;
    }
}
