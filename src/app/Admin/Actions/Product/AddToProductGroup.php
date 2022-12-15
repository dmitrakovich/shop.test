<?php

namespace App\Admin\Actions\Product;

use App\Services\Product\ProductGroupService;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class AddToProductGroup extends Action
{
    protected $selector = '.add-product-group';

    public $name = 'Добавить в группу товаров';

    private $productId;

    public function __construct(?int $productId = null)
    {
        parent::__construct();
        if ($productId) {
            $this->productId = $productId;
        }
    }

    public function handle(
        Request $request
    ) {
        $service = new ProductGroupService;
        $data = $request->all();
        $service->addToProductGroup($data['cur_product_id'], $data['product_id']);

        return $this->response()->success('Товар добавлен в группу товаров!')->refresh();
    }

    public function form()
    {
        $this->hidden('cur_product_id', 'Номер заказа')->default($this->productId ?? null);
        $this->text('product_id', 'ID товара')->rules('required|exists:products,id');
    }

    public function html()
    {
        return "<div class='text-center'><a class='add-product-group btn btn-success'>" . $this->name . '</a></div>';
    }
}
