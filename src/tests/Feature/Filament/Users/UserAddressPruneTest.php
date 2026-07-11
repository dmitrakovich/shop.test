<?php

namespace Tests\Feature\Filament\Users;

use App\Models\User\Group;
use App\Models\User\User;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAddressPruneTest extends TestCase
{
    use RefreshDatabase;

    public function test_prunes_blank_address_stubs_when_filled_addresses_exist(): void
    {
        $user = $this->createUser();

        $user->addresses()->create([]);
        $user->addresses()->create([
            'city' => 'Минск',
            'address' => 'ул. Тестовая, д. 1',
        ]);

        $user->pruneEmptyAddresses();

        $this->assertCount(1, $user->addresses()->get());
        $this->assertSame('ул. Тестовая, д. 1', $user->addresses()->first()?->address);
    }

    public function test_keeps_sole_blank_address_stub(): void
    {
        $user = $this->createUser();
        $user->addresses()->create([]);

        $user->pruneEmptyAddresses();

        $this->assertCount(1, $user->addresses()->get());
    }

    private function createUser(): User
    {
        $group = Group::query()->create([
            'name' => 'Тест',
            'discount' => 0,
        ]);

        return User::withoutEvents(fn (): User => User::query()->create([
            'group_id' => $group->id,
            'first_name' => 'Тест',
            'last_name' => 'Юзер',
            'phone' => Phone::fromRawString('+375291112233'),
        ]));
    }
}
