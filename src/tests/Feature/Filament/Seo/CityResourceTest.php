<?php

namespace Tests\Feature\Filament\Seo;

use App\Filament\Resources\Seo\Cities\Pages\CreateCity;
use App\Filament\Resources\Seo\Cities\Pages\EditCity;
use App\Filament\Resources\Seo\Cities\Pages\ListCities;
use App\Models\Admin\AdminUser;
use App\Models\City;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CityResourceTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    private Country $country;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createSuperAdmin();
        $this->country = Country::factory()->create([
            'name' => 'Беларусь',
            'code' => 'BY',
        ]);
    }

    public function test_cities_list_page_can_be_rendered(): void
    {
        $city = City::factory()->for($this->country)->create([
            'name' => 'Минск',
            'catalog_title' => 'в Минске',
        ]);

        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(ListCities::class);
        $component->assertSuccessful();
        $component->assertCanSeeTableRecords([$city]);
    }

    public function test_city_can_be_created(): void
    {
        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(CreateCity::class);
        $component->assertSuccessful();
        $component
            ->fillForm([
                'country_id' => $this->country->id,
                'name' => 'Брест',
                'catalog_title' => 'в Бресте',
            ])
            ->call('create')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $city = City::query()->where('name', 'Брест')->first();

        $this->assertNotNull($city);
        $this->assertSame($this->country->id, $city->country_id);
        $this->assertSame('brest', $city->slug);
        $this->assertSame('в Бресте', $city->catalog_title);
    }

    public function test_city_can_be_updated(): void
    {
        $city = City::factory()->for($this->country)->create([
            'name' => 'Гродно',
            'catalog_title' => 'в Гродно',
        ]);

        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(EditCity::class, ['record' => $city->getKey()]);
        $component->assertSuccessful();
        $component
            ->fillForm([
                'country_id' => $this->country->id,
                'name' => 'Гомель',
                'catalog_title' => 'в Гомеле',
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $city->refresh();

        $this->assertSame('Гомель', $city->name);
        $this->assertSame('gomel', $city->slug);
        $this->assertSame('в Гомеле', $city->catalog_title);
    }

    public function test_city_name_is_required(): void
    {
        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(CreateCity::class);
        $component
            ->fillForm([
                'country_id' => $this->country->id,
                'name' => '',
                'catalog_title' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['name' => 'required']);
    }

    private function createSuperAdmin(): AdminUser
    {
        $admin = AdminUser::query()->create([
            'username' => 'city_admin',
            'password' => bcrypt('secret'),
            'name' => 'City Admin',
        ]);

        $role = Role::findOrCreate('super_admin', 'admin');
        $admin->assignRole($role);

        return $admin;
    }
}
