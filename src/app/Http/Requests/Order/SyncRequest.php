<?php

namespace App\Http\Requests\Order;

use App\Http\Requests\SyncRequestTrait;
use App\Models\Orders\OrderItemStatus;
use App\Models\Orders\OrderStatus;
use Illuminate\Validation\Rule;

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
            'user_id' => $this->user_id ?? null,
            'country_id' => empty($this->country) ? null : 1,
            'region' => $this->state,
            'order_method' => $this->getOrderMethod(),
            'status_key' => $this->status_key ?? OrderStatus::getDefaultValue(),

            'utm_medium' => 'refferer',
            'utm_source' => 'modny.by',
            'utm_campaign' => 'orderoldsite',
            'utm_content' => null,
            'utm_term' => null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
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
