<?php

namespace App\Http\Requests;

use App\Models\Feedback;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class FeedbackRequest extends FormRequest
{
    /**
     * Max photo sizes in kilobytes
     */
    final const MAX_PHOTO_SIZE = 5_000;

    /**
     * Max video sizes in kilobytes
     */
    final const MAX_VIDEO_SIZE = 50_000;

    /**
     * Available mimetypes for video files
     */
    final const VIDEO_MIMETYPES = [
        'video/mp4',
        'video/avi',
        'video/mpeg',
        'video/quicktime',
    ];

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
            'type' => $captchaScore > 4 ? Feedback::TYPE_REVIEW : Feedback::TYPE_SPAM,
            'captcha_score' => intval($captchaScore),
            'rating' => intval($this->rating ?? 5),
        ]);
    }

    /**
     * Get the validated data from the request.
     *
     * @return array
     */
    public function validated($key = null, $default = null)
    {
        return array_merge($this->validator->validated(), [
            'user_id' => Auth::id(),
            'ip' => $this->ip(),
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
            'user_city' => ['required', 'max:255'],
            'text' => ['required'],
            'rating' => ['integer', 'between:0,5'],
            'product_id' => ['integer', 'min:0'],
            'type' => [],
            'captcha_score' => ['integer', 'between:0,10'],
            'photos' => ['array', 'max:10'],
            'photos.*' => ['image', 'max:' . self::MAX_PHOTO_SIZE],
            'videos' => ['array', 'max:5'],
            'videos.*' => [
                'mimetypes:' . implode(',', self::VIDEO_MIMETYPES),
                'max:' . self::MAX_VIDEO_SIZE,
            ],
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
            'user_city' => '"город"',
            'text' => '"комментарий"',
            'photos' => '"фотографии"',
            'photos.*' => '"фотографии"',
            'videos' => '"видео"',
            'videos.*' => '"видео"',
        ];
    }
}
