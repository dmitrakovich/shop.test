<?php

namespace App\Http\Requests;

use App\Facades\Currency;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
        $this->merge([
            'user_name' => $this->user_name ?? $this->name,
            'type' => 'retail',
            'status' => $this->status ?? 0,
        ]);

        if (!$this->wantsJson()) {
            $this->merge([
                'user_id' => Auth::check() ? Auth::id() : null,
                'currency' => Currency::getCurrentCurrency()->code,
                'rate' => Currency::getCurrentCurrency()->rate,
                'created_at' => now()
            ]);
        }
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
            'user_name' => ['required', 'max:191'],
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
            'status' => ['integer'],
            'created_at' => ['date']
        ];
    }
}
