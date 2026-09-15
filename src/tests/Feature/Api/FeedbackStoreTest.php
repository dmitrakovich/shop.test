<?php

namespace Tests\Feature\Api;

use App\Events\ReviewPosted;
use App\Facades\Device;
use App\Models\Feedback;
use App\Models\User\Device as UserDevice;
use App\Models\User\Group;
use App\Models\User\User;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use ReflectionProperty;
use Tests\TestCase;

class FeedbackStoreTest extends TestCase
{
    use RefreshDatabase;

    private const string DEVICE_ID = '8d854825-6753-4a16-9056-9f36b7ac7b90';

    protected function setUp(): void
    {
        parent::setUp();

        (new ReflectionProperty(Device::class, 'currentDevice'))->setValue(null);
    }

    public function test_web_session_does_not_attach_user(): void
    {
        $user = $this->createUser('+375291112277');

        $this->actingAs($user)
            ->postJson('/api/v1/feedbacks', $this->payload(), $this->deviceHeaders())
            ->assertSuccessful();

        $feedback = Feedback::query()->latest('id')->first();

        $this->assertNotNull($feedback);
        $this->assertNull($feedback->user_id);
    }

    public function test_guest_review_is_stored_without_user(): void
    {
        $this->postJson('/api/v1/feedbacks', $this->payload(), $this->deviceHeaders())
            ->assertSuccessful();

        $feedback = Feedback::query()->latest('id')->first();

        $this->assertNotNull($feedback);
        $this->assertNull($feedback->user_id);
        $this->assertSame('Анна', $feedback->user_name);
    }

    public function test_sanctum_token_attaches_user_and_resets_review_cache(): void
    {
        $user = $this->createUser('+375291112233');
        Cache::put($user->getCacheKey(), 'stale');

        $this->withToken($user->createToken('api')->plainTextToken)
            ->postJson('/api/v1/feedbacks', $this->payload(), $this->deviceHeaders())
            ->assertSuccessful();

        $feedback = Feedback::query()->latest('id')->first();

        $this->assertNotNull($feedback);
        $this->assertSame($user->id, $feedback->user_id);
        $this->assertFalse(Cache::has($user->getCacheKey()));
    }

    public function test_device_user_is_attached_when_request_has_no_token(): void
    {
        $user = $this->createUser('+375291112244');
        UserDevice::query()->create([
            'api_id' => self::DEVICE_ID,
            'user_id' => $user->id,
        ]);

        $this->postJson('/api/v1/feedbacks', $this->payload(), $this->deviceHeaders())
            ->assertSuccessful();

        $feedback = Feedback::query()->latest('id')->first();

        $this->assertNotNull($feedback);
        $this->assertSame($user->id, $feedback->user_id);
    }

    public function test_sanctum_token_user_wins_over_device_user(): void
    {
        Event::fake([ReviewPosted::class]);

        $deviceUser = $this->createUser('+375291112255');
        $tokenUser = $this->createUser('+375291112266');
        UserDevice::query()->create([
            'api_id' => self::DEVICE_ID,
            'user_id' => $deviceUser->id,
        ]);

        $this->withToken($tokenUser->createToken('api')->plainTextToken)
            ->postJson('/api/v1/feedbacks', $this->payload(), $this->deviceHeaders())
            ->assertSuccessful();

        $feedback = Feedback::query()->latest('id')->first();

        $this->assertNotNull($feedback);
        $this->assertSame($tokenUser->id, $feedback->user_id);

        Event::assertDispatched(
            ReviewPosted::class,
            fn (ReviewPosted $event): bool => $event->user?->is($tokenUser) === true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'user_name' => 'Анна',
            'user_city' => 'Минск',
            'text' => 'Отличная пара',
            'rating' => 5,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function deviceHeaders(): array
    {
        return ['device-id' => self::DEVICE_ID];
    }

    private function createUser(string $phone): User
    {
        return User::withoutEvents(fn (): User => User::query()->create([
            'group_id' => Group::query()->where('discount', 0)->value('id'),
            'first_name' => 'Тест',
            'last_name' => 'Юзер',
            'phone' => Phone::fromRawString($phone),
        ]));
    }
}
