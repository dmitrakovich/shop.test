<?php

namespace App\Http\Requests;

use App\Services\OldSiteSyncService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Auth;

trait SyncRequestTrait
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Handle a failed validation attempt.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(Validator $validator): never
    {
        abort(OldSiteSyncService::errorResponse($validator->errors()->all()));
    }
}
