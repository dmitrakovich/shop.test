<?php

namespace Tests\Feature\Filament\Settings;

use App\Filament\Resources\Settings\InfoPages\Pages\CreateInfoPage;
use App\Filament\Resources\Settings\InfoPages\Pages\EditInfoPage;
use App\Filament\Resources\Settings\InfoPages\Pages\ListInfoPages;
use App\Models\Admin\AdminUser;
use App\Models\InfoPage;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InfoPageResourceTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createSuperAdmin();
    }

    public function test_info_pages_list_page_can_be_rendered(): void
    {
        $page = InfoPage::factory()->create([
            'name' => 'Доставка',
            'slug' => 'delivery',
            'html' => '<p>&nbsp;&nbsp;Доставка с <strong>примеркой</strong></p>',
        ]);

        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(ListInfoPages::class);
        $component->assertSuccessful();
        $component->assertCanSeeTableRecords([$page]);
        $component->assertSee('Доставка с примеркой');
        $component->assertDontSee('&nbsp;');
        $component->assertDontSee('<strong>');
    }

    public function test_info_page_can_be_created(): void
    {
        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(CreateInfoPage::class);
        $component->assertSuccessful();
        $component
            ->fillForm([
                'name' => 'Оплата',
                'slug' => 'payment',
                'html' => '<p>Способы оплаты</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $page = InfoPage::query()->where('slug', 'payment')->first();

        $this->assertNotNull($page);
        $this->assertSame('Оплата', $page->name);
        $this->assertSame('<p>Способы оплаты</p>', $page->html);
    }

    public function test_info_page_can_be_updated(): void
    {
        $page = InfoPage::factory()->create([
            'name' => 'Возврат',
            'slug' => 'return',
            'html' => '<p>Старый текст</p>',
        ]);

        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(EditInfoPage::class, ['record' => $page->getKey()]);
        $component->assertSuccessful();
        $component
            ->fillForm([
                'name' => 'Возврат товара',
                'slug' => 'returns',
                'html' => '<p>Новый текст</p>',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $page->refresh();

        $this->assertSame('Возврат товара', $page->name);
        $this->assertSame('returns', $page->slug);
        $this->assertSame('<p>Новый текст</p>', $page->html);
    }

    public function test_info_page_name_and_slug_are_required(): void
    {
        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(CreateInfoPage::class);
        $component
            ->fillForm([
                'name' => '',
                'slug' => '',
                'html' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
                'slug' => 'required',
            ]);
    }

    public function test_info_page_slug_must_be_unique(): void
    {
        InfoPage::factory()->create([
            'slug' => 'delivery',
        ]);

        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(CreateInfoPage::class);
        $component
            ->fillForm([
                'name' => 'Доставка',
                'slug' => 'delivery',
                'html' => '<p>Текст</p>',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);
    }

    public function test_info_page_can_be_deleted(): void
    {
        $page = InfoPage::factory()->create();

        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(EditInfoPage::class, ['record' => $page->getKey()]);
        $component
            ->callAction(DeleteAction::class)
            ->assertNotified();

        $this->assertModelMissing($page);
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'info_page_admin',
            'password' => bcrypt('secret'),
            'name' => 'Info Page Admin',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
