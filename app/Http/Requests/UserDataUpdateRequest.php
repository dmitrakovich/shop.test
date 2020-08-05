<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserDataUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'last_name' => ['max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'patronymic_name' => ['max:255'],
            'email' => ['email:filter', 'unique:users,email,' . auth()->id()],
            // 'phone' => [],
            'birth_date' => ['date', 'nullable'],
            'country' => ['integer'],
            // 'address' => []
        ];
    }
}
