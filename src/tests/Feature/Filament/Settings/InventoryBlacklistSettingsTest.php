<?php

namespace Tests\Feature\Filament\Settings;

use App\Enums\Config\ConfigKey;
use App\Filament\Pages\Settings\InventoryBlacklistSettings;
use App\Models\Admin\AdminUser;
use App\Models\AvailableSizes;
use App\Models\Config;
use Filament\Forms\Components\TagsInput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryBlacklistSettingsTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createSuperAdmin();
    }

    public function test_inventory_blacklist_page_shows_saved_categories(): void
    {
        $categories = Config::value(ConfigKey::InventoryBlacklist, 'categories');

        $this->assertNotEmpty($categories);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(InventoryBlacklistSettings::class)
            ->assertSuccessful()
            ->assertSee('Категории, исключённые из синхронизации с 1С')
            ->assertSee('Категории 1С')
            ->assertFormSet(['categories' => $categories]);
    }

    public function test_inventory_blacklist_suggests_categories_from_last_1c_upload(): void
    {
        AvailableSizes::query()->insert([
            ['sku' => 'A-1', 'buy_price' => 10, 'sell_price' => 20, 'category_name' => 'Шнурки'],
            ['sku' => 'A-2', 'buy_price' => 10, 'sell_price' => 20, 'category_name' => 'Кроссовки'],
            ['sku' => 'A-3', 'buy_price' => 10, 'sell_price' => 20, 'category_name' => 'Кроссовки'],
            ['sku' => 'A-4', 'buy_price' => 10, 'sell_price' => 20, 'category_name' => null],
        ]);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(InventoryBlacklistSettings::class)
            ->assertFormFieldExists('categories', function (TagsInput $field): bool {
                return $field->getSuggestions() === ['Кроссовки', 'Шнурки'];
            });
    }

    public function test_inventory_blacklist_saves_trimmed_unique_categories_and_clears_cache(): void
    {
        Config::findCacheable(ConfigKey::InventoryBlacklist);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(InventoryBlacklistSettings::class)
            ->fillForm([
                'categories' => [' Стельки ', 'Крем', 'Стельки', '', 'Крем, гель'],
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified('Список исключённых категорий сохранён');

        $this->assertFalse(Cache::has('config.' . ConfigKey::InventoryBlacklist->value));
        $this->assertSame(
            ['categories' => ['Стельки', 'Крем', 'Крем, гель']],
            Config::findByKey(ConfigKey::InventoryBlacklist)?->config,
        );
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'inventory_blacklist_admin',
            'password' => bcrypt('secret'),
            'name' => 'Inventory Blacklist',
        ]);

        $admin->assignRole(Role::findOrCreate('super_admin', 'admin'));

        return $admin;
    }
}
