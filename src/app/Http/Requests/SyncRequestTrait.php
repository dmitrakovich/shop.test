<?php

namespace App\Http\Requests;

use App\Services\OldSiteSyncService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Validation\Validator;

trait SyncRequestTrait
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check();
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
