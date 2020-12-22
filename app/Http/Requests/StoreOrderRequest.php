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
            'name' => 'required|max:200',
            'email' => 'email|nullable',
            'phone' => 'required',
            'payment_name' => 'nullable',
            'payment_code' => 'nullable',
            'delivery_name' => 'nullable',
            'delivery_code' => 'nullable',
            'city' =>'nullable',
            'user_addr' => 'nullable',
        ];
    }
}
