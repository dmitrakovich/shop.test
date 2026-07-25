<?php

namespace Tests\Feature;

use App\Enums\Feedback\FeedbackType;
use App\Enums\Feedback\ReviewDiscountType;
use App\Enums\Order\OrderStatus;
use App\Models\Feedback;
use App\Models\Product;
use App\Models\User\Group;
use App\Models\User\User;
use App\Services\SaleService;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class ReviewDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_photo_review_after_order_gives_photo_discount(): void
    {
        $user = $this->createUserWithReviewMedia('photos');

        $this->actingAs($user);
        $this->app->forgetInstance(SaleService::class);

        $product = Product::factory()->create([
            'price' => 100,
            'old_price' => 0,
        ]);
        $product->applySales();

        $sale = $product->getSale(SaleService::REVIEW_SALE_KEY);

        $this->assertNotNull($sale);
        $this->assertSame(10.0, $sale->discount);
        $this->assertSame(90.0, $sale->price);
        $this->assertTrue($user->hasReviewAfterOrder());
        $this->assertSame(ReviewDiscountType::Photo, $user->getReviewDiscountType());
    }

    public function test_video_review_after_order_gives_video_discount(): void
    {
        $user = $this->createUserWithReviewMedia('videos');

        $this->actingAs($user);
        $this->app->forgetInstance(SaleService::class);

        $product = Product::factory()->create([
            'price' => 100,
            'old_price' => 0,
        ]);
        $product->applySales();

        $sale = $product->getSale(SaleService::REVIEW_SALE_KEY);

        $this->assertNotNull($sale);
        $this->assertSame(20.0, $sale->discount);
        $this->assertSame(80.0, $sale->price);
        $this->assertTrue($user->hasReviewAfterOrder());
        $this->assertSame(ReviewDiscountType::Video, $user->getReviewDiscountType());
    }

    public function test_review_without_media_does_not_give_discount(): void
    {
        $user = $this->createUser();
        $this->createOrderForUser($user, now()->subDay());
        $this->createFeedbackForUser($user, now());

        $this->actingAs($user);
        $this->app->forgetInstance(SaleService::class);

        $product = Product::factory()->create([
            'price' => 100,
            'old_price' => 0,
        ]);
        $product->applySales();

        $this->assertNull($product->getSale(SaleService::REVIEW_SALE_KEY));
        $this->assertFalse($user->hasReviewAfterOrder());
        $this->assertNull($user->getReviewDiscountType());
    }

    private function createUserWithReviewMedia(string $collection): User
    {
        $user = $this->createUser();
        $this->createOrderForUser($user, now()->subDay());
        $feedback = $this->createFeedbackForUser($user, now());
        $this->attachMedia($feedback, $collection);

        Cache::forget($user->getCacheKey());

        return $user->fresh();
    }

    private function createUser(): User
    {
        return User::withoutEvents(fn (): User => User::query()->create([
            'group_id' => Group::query()->where('discount', 0)->value('id'),
            'first_name' => 'Тест',
            'last_name' => 'Юзер',
            'phone' => Phone::fromRawString('+375291112233'),
        ]));
    }

    private function createOrderForUser(User $user, \Illuminate\Support\Carbon $createdAt): void
    {
        $user->orders()->create([
            'first_name' => 'Тест',
            'phone' => Phone::fromRawString('+375291112233'),
            'total_price' => 100,
            'currency' => 'BYN',
            'rate' => 1,
            'status' => OrderStatus::COMPLETED,
            'status_updated_at' => $createdAt,
            'created_at' => $createdAt,
        ]);
    }

    private function createFeedbackForUser(User $user, \Illuminate\Support\Carbon $createdAt): Feedback
    {
        return Feedback::query()->create([
            'user_id' => $user->id,
            'user_name' => 'Тест',
            'text' => 'Отзыв',
            'rating' => 5,
            'product_id' => 0,
            'type' => FeedbackType::REVIEW,
            'publish' => true,
            'ip' => '127.0.0.1',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function attachMedia(Feedback $feedback, string $collection): void
    {
        Media::query()->create([
            'model_type' => Feedback::class,
            'model_id' => $feedback->id,
            'uuid' => (string)Str::uuid(),
            'collection_name' => $collection,
            'name' => 'review',
            'file_name' => $collection === 'videos' ? 'review.mp4' : 'review.jpg',
            'mime_type' => $collection === 'videos' ? 'video/mp4' : 'image/jpeg',
            'disk' => 'public',
            'conversions_disk' => 'public',
            'size' => 1024,
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
        ]);
    }
}
