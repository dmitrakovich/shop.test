<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\SyncRequestTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class SyncRequest extends FormRequest
{
    use SyncRequestTrait;

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'remember_token' => Str::random(60),
            'country_id' => empty($this->country) ? null : 1,
            'region' => $this->state,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id' => ['integer', 'required'],
            'password' => ['required'],
            'remember_token' => ['string'],
            'usergroup_id' => ['integer', 'nullable'],
            'first_name' => ['required', 'max:50'],
            'patronymic_name' => ['nullable', 'max:50'],
            'last_name' => ['nullable', 'max:50'],
            'phone' => ['nullable', 'max:20'],
            'created_at' => ['date', 'nullable'],
            'email' => ['email', 'max:191', 'nullable'],
            'country_id' => ['integer', 'nullable'],
            'region' => ['nullable', 'max:191'],
            'city' => ['nullable', 'max:191'],
            'address' => ['nullable', 'max:191'],
        ];
    }
}
