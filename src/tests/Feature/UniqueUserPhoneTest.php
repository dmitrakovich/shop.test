<?php

namespace Tests\Feature;

use App\Models\User\User;
use App\Rules\PhoneNumber;
use App\Rules\UniqueUserPhone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UniqueUserPhoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_a_phone_used_by_another_user_after_normalization(): void
    {
        User::withoutEvents(fn () => User::factory()->create([
            'phone' => '+375291234567',
        ]));

        $validator = Validator::make([
            'phone' => '+375 (29) 123-45-67',
        ], [
            'phone' => [new UniqueUserPhone()],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }

    public function test_it_allows_the_current_users_phone_when_ignored(): void
    {
        $user = User::withoutEvents(fn () => User::factory()->create([
            'phone' => '+375291234567',
        ]));

        $validator = Validator::make([
            'phone' => '+375 (29) 123-45-67',
        ], [
            'phone' => [new UniqueUserPhone($user->id)],
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_phone_number_rule_handles_unparseable_values_as_validation_failures(): void
    {
        $validator = Validator::make([
            'phone' => 'not-a-phone',
        ], [
            'phone' => [new PhoneNumber()],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('phone', $validator->errors()->toArray());
    }
}
