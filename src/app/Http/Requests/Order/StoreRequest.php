<?php

namespace App\Http\Requests\Order;

use App\Facades\Currency;
use Illuminate\Validation\Rule;
use App\Models\Enum\OrderMethod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

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
            'user_id' => Auth::check() ? Auth::id() : null,
            'currency' => Currency::getCurrentCurrency()->code,
            'rate' => Currency::getCurrentCurrency()->rate,
            'order_method' => $this->getOrderMethod(),

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
            'user_id' => ['integer', 'nullable'],
            'first_name' => ['required', 'max:50'],
            'patronymic_name' => ['nullable', 'max:50'],
            'last_name' => ['nullable', 'max:50'],
            'order_method' => [Rule::in(OrderMethod::getValues())],
            'email' => ['email', 'nullable', 'max:50'],
            'phone' => ['required', 'max:191'],
            'comment' => ['nullable'],
            'currency' => ['required', 'string', 'max:5'],
            'rate' => ['required'],
            'payment_id' => ['integer', 'nullable'],
            'delivery_id' => ['integer', 'nullable'],
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
     *
     * @return string
     */
    protected function getOrderMethod(): string
    {
        return $this->order_method
            ?? ($this->has(['product_id', 'sizes']) ? OrderMethod::ONECLICK : null)
            ?? OrderMethod::DEFAULT;
    }

    /**
     * Check is this order made in one click
     *
     * @return boolean
     */
    public function isOneClick(): bool
    {
        return $this->order_method == OrderMethod::ONECLICK;
    }
}
