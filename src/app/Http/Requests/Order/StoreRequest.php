<?php

namespace App\Http\Requests\Order;

use App\Enums\Order\OrderMethod;
use App\Facades\Currency;
use App\Models\Data\OrderData;
use Deliveries\ShopPvz;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $utm = json_decode($this->cookie('utm'), true);

        $this->merge([
            'currency' => Currency::getCurrentCurrency()->code,
            'rate' => Currency::getCurrentCurrency()->rate,
            'order_method' => $this->getOrderMethod()->value,
            'stock_id' => $this->getStockId(),

            'utm_medium' => $utm['utm_medium'] ?? null,
            'utm_source' => $utm['utm_source'] ?? null,
            'utm_campaign' => $utm['utm_campaign'] ?? null,
            'utm_content' => $utm['utm_content'] ?? null,
            'utm_term' => $utm['utm_term'] ?? null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => ['required', 'max:50'],
            'patronymic_name' => ['nullable', 'max:50'],
            'last_name' => ['nullable', 'max:50'],
            'order_method' => [Rule::enum(OrderMethod::class)],
            'email' => ['email', 'nullable', 'max:50'],
            'phone' => ['required', 'max:191'],
            'comment' => ['nullable'],
            'currency' => ['required', 'string', 'max:5'],
            'rate' => ['required'],
            'payment_id' => ['integer', 'nullable'],
            'delivery_id' => ['integer', 'nullable'],
            'stock_id' => ['integer', 'nullable'],
            'country_id' => ['integer', 'nullable'],
            'region' => ['nullable', 'max:50'],
            'city' => ['nullable', 'max:50'],
            'zip' => ['nullable', 'max:10'],
            'user_addr' => ['nullable', 'max:191'],
            'utm_medium' => ['nullable'],
            'utm_source' => ['nullable'],
            'utm_campaign' => ['nullable'],
            'utm_content' => ['nullable'],
            'utm_term' => ['nullable'],
        ];
    }

    /**
     * Get order method
     */
    protected function getOrderMethod(): OrderMethod
    {
        return $this->order_method
            ?? ($this->has(['product_id', 'sizes']) ? OrderMethod::ONECLICK : null)
            ?? OrderMethod::DEFAULT;
    }

    /**
     * Check is this order made in one click
     */
    public function isOneClick(): bool
    {
        return $this->order_method === OrderMethod::ONECLICK->value;
    }

    /**
     * Get the stock ID if the delivery method is ShopPvz.
     */
    public function getStockId(): ?int
    {
        if ($this->integer('delivery_id') === ShopPvz::ID) {
            return $this->integer('stock_id');
        }

        return null;
    }

    /**
     * Validate request & make DTO order object
     */
    public function getValidatedData(): OrderData
    {
        return new OrderData(...$this->validated());
    }
}
