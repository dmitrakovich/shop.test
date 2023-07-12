<?php

namespace App\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeUserByPhoneRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'phone' => 'required|exists:users,phone',
        ];
    }

    /**
     * Получить сообщения об ошибках для определенных правил валидации.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'phone.required' => 'Поле "Телефон" обязательно для заполнения.',
            'phone.exists' => 'Пользователь с таким телефоном не найден.',
        ];
    }
}
