<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'last_name' => ['max:50'],
            'first_name' => ['required', 'string', 'max:50'],
            'patronymic_name' => ['max:50'],
            'email' => ['email:filter', 'max:191', 'unique:users,email,' . auth()->id()],
            'phone' => ['nullable', 'min:7', 'max:20', 'unique:users,phone,' . auth()->id()],
            'birth_date' => ['date', 'nullable'],
            'country_id' => ['integer'],
            'address' => ['nullable', 'max:191'],
        ];
    }
}
