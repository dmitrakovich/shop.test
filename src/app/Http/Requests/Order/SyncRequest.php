<?php

namespace App\Http\Requests\Order;

use Illuminate\Validation\Rule;
use App\Models\Enum\OrderStatus;
use App\Http\Requests\SyncRequestTrait;
use App\Models\Enum\OrderItemStatus;

class SyncRequest extends StoreRequest
{
    use SyncRequestTrait;

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'country_id' => empty($this->country) ? null : 1,
            'region' => $this->state,
            'order_method' => $this->getOrderMethod(),
            'status' => $this->status ?? OrderStatus::CREATED,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            'total_price' => ['numeric', 'required'],
            'status' => ['required', Rule::in(OrderStatus::getValues())],
            'created_at' => ['date'],
            'items' => ['array', 'required'],
            'items.*.product_id' => ['integer', 'required'],
            'items.*.count' => ['numeric', 'required'],
            'items.*.price' => ['required'],
            'items.*.size' => ['integer', 'required'],
            'items.*.status' => ['required', Rule::in(OrderItemStatus::getValues())],
        ]);
    }
}
