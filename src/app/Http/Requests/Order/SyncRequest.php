<?php

namespace App\Http\Requests\Order;

use Illuminate\Validation\Rule;
use App\Models\Orders\OrderStatus;
use App\Models\Orders\OrderItemStatus;
use App\Http\Requests\SyncRequestTrait;

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
            'status_key' => $this->status_key ?? OrderStatus::getDefaultValue(),
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
            'status_key' => ['required', Rule::in(OrderStatus::getValues())],
            'created_at' => ['date'],
            'items' => ['array', 'required'],
            'items.*.product_id' => ['integer', 'required'],
            'items.*.count' => ['numeric', 'required'],
            'items.*.price' => ['required'],
            'items.*.size' => ['integer', 'required'],
            'items.*.status_key' => ['required', Rule::in(OrderItemStatus::getValues())],
        ]);
    }
}
