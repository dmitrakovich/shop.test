<?php

namespace Tests\Feature\Filament\Settings;

use App\Filament\Resources\Settings\PaymentMethods\Pages\EditPaymentMethod;
use App\Filament\Resources\Settings\PaymentMethods\Pages\ListPaymentMethods;
use App\Models\Admin\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Payments\PaymentMethod;
use Tests\TestCase;

class PaymentMethodResourceTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = AdminUser::query()->create([
            'username' => 'payment_admin',
            'password' => bcrypt('secret'),
            'name' => 'Payment',
        ]);
    }

    public function test_payment_methods_list_page_can_be_rendered(): void
    {
        PaymentMethod::query()->create([
            'name' => 'Картой',
            'instance' => 'Card',
            'active' => true,
            'sorting' => 0,
        ]);

        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(ListPaymentMethods::class);
        $component->assertSuccessful();
        $component->assertCanSeeTableRecords(PaymentMethod::query()->get());
    }

    public function test_payment_method_can_be_updated(): void
    {
        $method = PaymentMethod::query()->create([
            'name' => 'Картой',
            'instance' => 'Card',
            'active' => false,
            'sorting' => 0,
        ]);

        $this->actingAs($this->admin, 'admin');

        $component = Livewire::test(EditPaymentMethod::class, ['record' => $method->getKey()]);
        $component->assertSuccessful();
        $component
            ->fillForm([
                'name' => 'Банковской картой',
                'active' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        $method->refresh();

        $this->assertSame('Банковской картой', $method->name);
        $this->assertTrue($method->active);
        $this->assertSame('Card', $method->instance);
    }
}
