<?php

namespace App\Admin\Selectable;

use App\Models\Product as ProductModel;
use Encore\Admin\Grid\Selectable;

/**
 * @mixin ProductModel
 */
class Product extends Selectable
{
    public $model = ProductModel::class;

    public function make()
    {
        $this->model()->withTrashed()->with('media');

        $this->column('media', 'Фото')->display(
            fn () => $this->getFirstMediaUrl('default', 'thumb')
        )->image();
        $this->column('id', 'Id');
        $this->column('category.title', 'Категория');
        $this->column('sku', 'Артикул');
    }
}
