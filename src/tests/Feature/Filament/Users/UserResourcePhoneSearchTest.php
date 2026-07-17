<?php

namespace Tests\Feature\Filament\Users;

use App\Filament\Resources\Users\Users\Pages\ListUsers;
use App\Models\Admin\AdminUser;
use App\Models\User\Group;
use App\Models\User\User;
use App\ValueObjects\Phone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourcePhoneSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_be_searched_by_phone(): void
    {
        $group = Group::query()->create([
            'name' => 'Тест',
            'discount' => 0,
        ]);

        $matchingUser = User::withoutEvents(fn (): User => User::query()->create([
            'group_id' => $group->id,
            'first_name' => 'Анна',
            'last_name' => 'Иванова',
            'phone' => Phone::fromRawString('+375291112233'),
        ]));

        $otherUser = User::withoutEvents(fn (): User => User::query()->create([
            'group_id' => $group->id,
            'first_name' => 'Борис',
            'last_name' => 'Петров',
            'phone' => Phone::fromRawString('+375297778899'),
        ]));

        $admin = $this->createSuperAdmin();
        $this->actingAs($admin, 'admin');

        Livewire::test(ListUsers::class)
            ->assertCanSeeTableRecords([$matchingUser, $otherUser])
            ->searchTable('+375 29 111-22-33')
            ->assertCanSeeTableRecords([$matchingUser])
            ->assertCanNotSeeTableRecords([$otherUser])
            ->searchTable('7778899')
            ->assertCanSeeTableRecords([$otherUser])
            ->assertCanNotSeeTableRecords([$matchingUser]);
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'users_phone_admin',
            'password' => bcrypt('secret'),
            'name' => 'Users Phone Admin',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
