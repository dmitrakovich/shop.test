<?php

namespace Tests\Feature\Filament\Settings;

use App\Filament\Resources\Settings\DeliveryMethods\Pages\EditDeliveryMethod;
use App\Filament\Resources\Settings\DeliveryMethods\Pages\ListDeliveryMethods;
use App\Models\Admin\AdminUser;
use Deliveries\DeliveryMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DeliveryMethodResourceTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = AdminUser::query()->create([
            'username' => 'delivery_admin',
            'password' => bcrypt('secret'),
            'name' => 'Delivery',
        ]);
    }

    public function test_delivery_methods_list_page_can_be_rendered(): void
    {
        DeliveryMethod::query()->create([
            'name' => 'Белпочта',
            'instance' => 'Belpost',
            'active' => true,
            'sorting' => 0,
        ]);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(ListDeliveryMethods::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords(DeliveryMethod::query()->get());
    }

    public function test_delivery_method_can_be_updated(): void
    {
        $method = DeliveryMethod::query()->create([
            'name' => 'Белпочта',
            'instance' => 'Belpost',
            'active' => false,
            'sorting' => 0,
        ]);

        $this->actingAs($this->admin, 'admin');

        Livewire::test(EditDeliveryMethod::class, ['record' => $method->getKey()])
            ->assertSuccessful()
            ->assertFormSet([
                'name' => 'Белпочта',
                'instance' => 'Belpost',
                'active' => false,
            ])
            ->fillForm([
                'name' => 'Белпочта курьер',
                'active' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $method->refresh();

        $this->assertSame('Белпочта курьер', $method->name);
        $this->assertTrue($method->active);
        $this->assertSame('Belpost', $method->getRawOriginal('instance'));
    }
}
