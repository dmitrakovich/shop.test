<?php

namespace Tests\Feature\Filament\Settings;

use App\Filament\Resources\Settings\Currencies\Pages\EditCurrency;
use App\Filament\Resources\Settings\Currencies\Pages\ListCurrencies;
use App\Models\Admin\AdminUser;
use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CurrencyResourceTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createSuperAdmin();
    }

    public function test_currencies_list_page_can_be_rendered(): void
    {
        Currency::query()->create([
            'code' => 'BYN',
            'country' => 'BY',
            'rate' => 1,
            'decimals' => 2,
            'symbol' => 'byn',
        ]);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(ListCurrencies::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(Currency::query()->get());
    }

    public function test_currency_rate_can_be_updated_and_cache_is_cleared(): void
    {
        $currency = Currency::query()->create([
            'code' => 'USD',
            'country' => 'US',
            'rate' => 0.40,
            'decimals' => 0,
            'symbol' => '$',
        ]);

        Cache::put('currencies', ['USD' => $currency]);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(EditCurrency::class, ['record' => $currency->getKey()])
            ->assertSuccessful()
            ->fillForm([
                'country' => 'US',
                'rate' => 0.55,
                'decimals' => 0,
                'symbol' => '$',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $currency->refresh();

        $this->assertEqualsWithDelta(0.55, $currency->rate, 0.001);
        $this->assertFalse(Cache::has('currencies'));
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'currency_admin',
            'password' => bcrypt('secret'),
            'name' => 'Currency',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
