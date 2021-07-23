<?php

namespace App\Http\Requests;

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
        list($deliveryCode, $deliveryName) = explode('|', $this->delivery ?? '|');
        list($paymentCode, $paymentName) = explode('|', $this->payment ?? '|');

        $this->merge([
            'payment_name' => $paymentName,
            'payment_code' => $paymentCode,
            'delivery_name' => $deliveryName,
            'delivery_code' => $deliveryCode,
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
            'name' => 'required|max:191',
            'email' => 'email|nullable|max:50',
            'phone' => 'required|max:20',
            'payment_name' => 'nullable',
            'payment_code' => 'nullable',
            'delivery_name' => 'nullable',
            'delivery_code' => 'nullable',
            'city' =>'nullable|max:50',
            'user_addr' => 'nullable|max:191',
        ];
    }
}
