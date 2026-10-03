<?php

namespace Tests\Feature\Api;

use App\Enums\Feedback\FeedbackType;
use App\Facades\Device;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\Product;
use App\Models\User\Device as UserDevice;
use App\Models\User\Group;
use App\Models\User\User;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionProperty;
use Tests\TestCase;

class FeedbackIndexTest extends TestCase
{
    use RefreshDatabase;

    private const string DEVICE_ID = '8d854825-6753-4a16-9056-9f36b7ac7b90';

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);
    }

    public function test_published_review_omits_internal_fields(): void
    {
        $categoryId = Category::query()->whereKeyNot(Category::ROOT_CATEGORY_ID)->value('id');
        $product = Product::factory()->published()->create([
            'category_id' => $categoryId,
            'buy_price' => 1234.56,
            'one_c_id' => 987654321,
        ]);
        $user = User::withoutEvents(fn (): User => User::query()->create([
            'group_id' => Group::query()->where('discount', 0)->value('id'),
            'first_name' => 'Тест',
            'last_name' => 'Юзер',
            'phone' => Phone::fromRawString('+375291119900'),
        ]));
        $device = UserDevice::query()->create(['api_id' => self::DEVICE_ID]);

        $published = Feedback::query()->create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'user_name' => 'Анна',
            'user_city' => 'Минск',
            'text' => 'Отличная пара',
            'rating' => 5,
            'product_id' => $product->id,
            'type' => FeedbackType::REVIEW,
            'captcha_score' => 9,
            'publish' => true,
            'ip' => '203.0.113.10',
        ]);

        Feedback::query()->create([
            'user_name' => 'Скрытый',
            'text' => 'Не опубликован',
            'rating' => 1,
            'product_id' => $product->id,
            'type' => FeedbackType::REVIEW,
            'captcha_score' => 3,
            'publish' => false,
            'ip' => '203.0.113.11',
        ]);

        $response = $this->getJson('/api/v1/feedbacks', [
            'device-id' => self::DEVICE_ID,
        ]);

        $response->assertOk()
            ->assertJsonCount(1, 'feedbacks')
            ->assertJsonPath('feedbacks.0.id', $published->id)
            ->assertJsonPath('feedbacks.0.user_name', 'Анна')
            ->assertJsonPath('feedbacks.0.user_city', 'Минск')
            ->assertJsonPath('feedbacks.0.text', 'Отличная пара')
            ->assertJsonPath('feedbacks.0.rating', 5)
            ->assertJsonPath('feedbacks.0.product.id', $product->id)
            ->assertJsonMissingPath('feedbacks.0.ip')
            ->assertJsonMissingPath('feedbacks.0.captcha_score')
            ->assertJsonMissingPath('feedbacks.0.user_id')
            ->assertJsonMissingPath('feedbacks.0.device_id')
            ->assertJsonMissingPath('feedbacks.0.product.buy_price')
            ->assertJsonMissingPath('feedbacks.0.product.one_c_id');

        $json = $response->getContent();
        $this->assertIsString($json);
        $this->assertStringNotContainsString('203.0.113.10', $json);
        $this->assertStringNotContainsString('203.0.113.11', $json);
        $this->assertStringNotContainsString('captcha_score', $json);
        $this->assertStringNotContainsString('buy_price', $json);
        $this->assertStringNotContainsString('one_c_id', $json);
        $this->assertStringNotContainsString('Не опубликован', $json);
    }
}
