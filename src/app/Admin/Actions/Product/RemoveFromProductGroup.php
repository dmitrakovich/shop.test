<?php

namespace App\Admin\Actions\Product;

use App\Services\Product\ProductGroupService;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class RemoveFromProductGroup extends Action
{
    protected $selector = '.remove-from-product-group';

    public $name = 'Удалить товар из группы товаров';

    private $productId;

    private $productGroupId;

    public function __construct(?int $productId = null, ?int $productGroupId = null)
    {
        parent::__construct();
        $this->productId = $productId;
        $this->productGroupId = $productGroupId;
    }

    public function handle(
        Request $request,
    ) {
        $service = new ProductGroupService;
        $data = $request->all();
        $service->removeFromProductGroup($data['product_id'], $data['product_group_id']);

        return $this->response()->success('Товар удален из группы товаров!')->refresh();
    }

    public function form()
    {
        $this->hidden('product_id', 'Номер заказа')->default($this->productId ?? null);
        $this->hidden('product_group_id', 'Номер заказа')->default($this->productGroupId ?? null);
    }

    public function html()
    {
        return '<div class="text-center"><a class="remove-from-product-group btn btn-danger">' . $this->name . '</a></div>';
    }
}
