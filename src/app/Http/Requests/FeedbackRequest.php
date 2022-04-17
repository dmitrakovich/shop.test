<?php

namespace App\Http\Requests;

use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class FeedbackRequest extends FormRequest
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
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $captchaScore = $this->captcha_score ?? 0;

        $this->merge([
            'type_id' => $captchaScore > 4 ? Feedback::TYPE_REVIEW : Feedback::TYPE_SPAM,
            'captcha_score' => intval($captchaScore),
            'rating' => intval($this->rating ?? 5),
            'product_id' => intval($this->product_id ?? 0)
        ]);
    }

    /**
     * Get the validated data from the request.
     *
     * @return array
     */
    public function validated()
    {
        return array_merge($this->validator->validated(), [
            'user_id' => Auth::id(),
            'yandex_id' => $this->cookie('_ym_uid'),
            'ip' => $this->ip()
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
            'user_name' => ['required', 'max:255'],
            'user_email' => ['email', 'max:255', 'nullable'],
            'text' => ['required'],
            'rating' => ['integer', 'between:0,5'],
            'product_id' => ['integer', 'min:0'],
            'type_id' => [],
            'captcha_score' => ['integer', 'between:0,10'],
            'photos' => ['array'],
            'photos.*' => ['image'],
            'videos' => ['array'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'user_name' => '"имя"',
            'text' => '"комментарий"',
            'photos' => '"фотографии"',
            'videos' => '"видео"',
        ];
    }
}
