<?php

namespace App\Data\Feedback;

use App\Data\Casts\ModelCast;
use App\Enums\Feedback\FeedbackType;
use App\Facades\Device;
use App\Models\Product;
use App\Models\User\User;
use App\Rules\VideoFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rules\ImageFile;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\Validation\Between;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class FeedbackData extends Data
{
    #[Max(255)]
    public string $userName;

    #[Max(255)]
    public string $userCity;

    public string $text;

    #[Between(0, 5)]
    public int $rating = 5;

    public ?int $productId = null;

    // todo: need withTrashed
    // #[MapInputName('product_id')]
    // #[WithCast(ModelCast::class, modelClass: Product::class)]
    // public ?Product $product;

    #[Computed]
    public FeedbackType $type;

    /** @var UploadedFile[] */
    #[Max(10)]
    public array $photos = [];

    /** @var UploadedFile[] */
    #[Max(5)]
    public array $videos = [];

    public function __construct(public int $captchaScore = 0)
    {
        $this->type = $captchaScore > 4 ? FeedbackType::REVIEW : FeedbackType::SPAM;
    }

    /** @return array<string, mixed> */
    public function with(): array
    {
        return [
            'user_id' => $this->resolveUser()?->id,
            'device_id' => Device::id(),
            'ip' => Request::ip(),
        ];
    }

    /**
     * Store is public (no auth:sanctum), so the default guard never sees the
     * Vue Bearer token. Read Sanctum explicitly, then the device-linked user.
     */
    private function resolveUser(): ?User
    {
        $user = Auth::guard('sanctum')->user();
        if ($user instanceof User) {
            return $user;
        }

        return Device::current()->getUser();
    }

    /** @return array<string, array<int, mixed>> */
    public static function rules(): array
    {
        return [
            'photos.*' => [new ImageFile()->max('5mb')],
            'videos.*' => [new VideoFile()->max('50mb')],
        ];
    }

    /** @return array<string, string> */
    public static function attributes(): array
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

    // protected function exceptProperties(): array
    // {
    //     return ['product', 'photos', 'videos'];
    // }
}
