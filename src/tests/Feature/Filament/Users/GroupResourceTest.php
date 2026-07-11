<?php

namespace Tests\Feature\Filament\Users;

use App\Filament\Resources\Users\Groups\Pages\ListGroups;
use App\Models\Admin\AdminUser;
use App\Models\User\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GroupResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_groups_list_page_can_be_rendered(): void
    {
        Group::query()->create([
            'name' => 'Новая регистрация',
            'discount' => 7,
        ]);

        $admin = AdminUser::query()->create([
            'username' => 'groups_admin',
            'password' => bcrypt('secret'),
            'name' => 'Groups',
        ]);

        $this->actingAs($admin, 'admin');

        $component = Livewire::test(ListGroups::class);
        $component->assertSuccessful();
        $component->assertCanSeeTableRecords(Group::query()->get());
    }

    public function test_groups_create_and_edit_pages_exist_but_are_not_registered(): void
    {
        $this->assertTrue(class_exists(\App\Filament\Resources\Users\Groups\Pages\CreateGroup::class));
        $this->assertTrue(class_exists(\App\Filament\Resources\Users\Groups\Pages\EditGroup::class));

        $pages = \App\Filament\Resources\Users\Groups\GroupResource::getPages();
        $this->assertArrayHasKey('index', $pages);
        $this->assertArrayNotHasKey('create', $pages);
        $this->assertArrayNotHasKey('edit', $pages);
    }
}
