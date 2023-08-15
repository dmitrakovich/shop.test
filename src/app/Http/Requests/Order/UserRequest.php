<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'email' => ['nullable', 'email', 'max:50'],
            'phone' => ['required', 'max:191'],
        ];
    }
}
