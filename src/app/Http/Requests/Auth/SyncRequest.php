<?php

namespace App\Http\Requests\Auth;

use Illuminate\Support\Str;
use App\Services\OldSiteSyncService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class SyncRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
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
     *
     * @return array
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
            'email' => ['email', 'nullable'],
            'country_id' => ['integer', 'nullable'],
            'region' => ['nullable', 'max:191'],
            'city' => ['nullable', 'max:191'],
            'address' => ['nullable', 'max:191'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        abort(OldSiteSyncService::errorResponse($validator->errors()->all()));
    }
}
