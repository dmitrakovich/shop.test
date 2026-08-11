<?php

namespace Tests\Feature\Auditing;

use App\Facades\Device as DeviceFacade;
use App\Models\Admin\AdminUser;
use App\Models\Audit;
use App\Models\ShortLink;
use App\Models\User\Address;
use App\Models\User\Device;
use App\Models\User\Group;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use ReflectionProperty;
use Tests\TestCase;

class AuditableModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_group_changes_are_audited(): void
    {
        $group = Group::query()->create([
            'name' => 'Audit group',
            'discount' => 5,
        ]);

        $this->assertDatabaseHas('audits', [
            'event' => 'created',
            'auditable_type' => Group::class,
            'auditable_id' => $group->id,
        ]);

        $group->update(['discount' => 10]);

        $audit = Audit::query()
            ->where('auditable_type', Group::class)
            ->where('auditable_id', $group->id)
            ->where('event', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);
        $this->assertEquals(5, $audit->old_values['discount'] ?? null);
        $this->assertEquals(10, $audit->new_values['discount'] ?? null);
    }

    public function test_admin_password_is_excluded_from_audit(): void
    {
        $admin = AdminUser::query()->create([
            'username' => 'audit_pwd',
            'password' => bcrypt('secret'),
            'name' => 'Audit',
        ]);

        $created = Audit::query()
            ->where('auditable_type', AdminUser::class)
            ->where('auditable_id', $admin->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($created);
        $this->assertArrayNotHasKey('password', $created->new_values ?? []);
        $this->assertArrayNotHasKey('remember_token', $created->new_values ?? []);

        $admin->update(['password' => bcrypt('new-secret'), 'name' => 'Audit Updated']);

        $updated = Audit::query()
            ->where('auditable_type', AdminUser::class)
            ->where('auditable_id', $admin->id)
            ->where('event', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($updated);
        $this->assertArrayNotHasKey('password', $updated->old_values ?? []);
        $this->assertArrayNotHasKey('password', $updated->new_values ?? []);
        $this->assertSame('Audit Updated', $updated->new_values['name'] ?? null);
    }

    public function test_guest_device_is_recorded_on_audit(): void
    {
        (new ReflectionProperty(DeviceFacade::class, 'currentDevice'))->setValue(null);

        $device = Device::query()->create([
            'api_id' => '8d854825-6753-4a16-9056-9f36b7ac7b90',
            'type' => \App\Enums\Device\DeviceType::MOBILE,
            'ip_address' => '127.0.0.1',
            'agent' => 'PHPUnit',
        ]);

        DeviceFacade::setDevice($device);

        $group = Group::query()->create([
            'name' => 'Guest device audit group',
            'discount' => 1,
        ]);

        $audit = Audit::query()
            ->where('auditable_type', Group::class)
            ->where('auditable_id', $group->id)
            ->where('event', 'created')
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame($device->id, (int)$audit->user_id);
        $this->assertSame($device->getMorphClass(), $audit->user_type);
        $this->assertSame('Device #' . $device->id, $audit->getUserLabel());
    }

    public function test_sanctum_authenticated_user_is_recorded_on_audit(): void
    {
        (new ReflectionProperty(DeviceFacade::class, 'currentDevice'))->setValue(null);

        $device = Device::query()->create([
            'api_id' => '8d854825-6753-4a16-9056-9f36b7ac7b91',
            'type' => \App\Enums\Device\DeviceType::MOBILE,
            'ip_address' => '127.0.0.1',
            'agent' => 'PHPUnit',
        ]);
        DeviceFacade::setDevice($device);

        $group = Group::query()->create([
            'name' => 'Sanctum audit group',
            'discount' => 0,
        ]);

        $user = User::query()->create([
            'group_id' => $group->id,
            'first_name' => 'Ivan',
            'phone' => 375291112244,
        ]);

        Sanctum::actingAs($user);

        $user->update(['first_name' => 'Petr']);

        $audit = Audit::query()
            ->where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('event', 'updated')
            ->latest('id')
            ->first();

        $this->assertNotNull($audit);
        $this->assertSame($user->id, (int)$audit->user_id);
        $this->assertSame($user->getMorphClass(), $audit->user_type);
    }

    public function test_user_otp_fields_are_excluded_from_audit(): void
    {
        $group = Group::query()->create([
            'name' => 'OTP group',
            'discount' => 0,
        ]);

        $user = User::query()->create([
            'group_id' => $group->id,
            'first_name' => 'Ivan',
            'phone' => 375291112233,
            'otp_code' => '1234',
            'otp_expires_at' => now()->addMinutes(5),
        ]);

        $created = Audit::query()
            ->where('auditable_type', User::class)
            ->where('auditable_id', $user->id)
            ->where('event', 'created')
            ->first();

        $this->assertNotNull($created);
        $this->assertArrayNotHasKey('otp_code', $created->new_values ?? []);
        $this->assertArrayNotHasKey('otp_expires_at', $created->new_values ?? []);

        $beforeCount = Audit::query()->where('auditable_type', User::class)->where('auditable_id', $user->id)->count();

        $user->update([
            'otp_code' => '9999',
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $afterCount = Audit::query()->where('auditable_type', User::class)->where('auditable_id', $user->id)->count();

        $this->assertSame($beforeCount, $afterCount);
    }

    public function test_short_link_hits_are_not_audited(): void
    {
        $link = ShortLink::query()->create([
            'short_link' => 'abcdefg',
            'full_link' => 'https://example.test/promo',
            'hits_count' => 0,
        ]);

        $beforeCount = Audit::query()
            ->where('auditable_type', ShortLink::class)
            ->where('auditable_id', $link->id)
            ->count();

        $link->recordHit();

        $afterCount = Audit::query()
            ->where('auditable_type', ShortLink::class)
            ->where('auditable_id', $link->id)
            ->count();

        $this->assertSame($beforeCount, $afterCount);
        $this->assertSame(1, $link->fresh()->hits_count);
    }

    public function test_address_creation_is_not_audited_but_updates_are(): void
    {
        $group = Group::query()->create([
            'name' => 'Address group',
            'discount' => 0,
        ]);

        $user = User::query()->create([
            'group_id' => $group->id,
            'first_name' => 'Petr',
            'phone' => 375299998877,
        ]);

        $address = Address::query()->create([
            'user_id' => $user->id,
            'city' => 'Minsk',
            'address' => 'Test st. 1',
        ]);

        $this->assertDatabaseMissing('audits', [
            'event' => 'created',
            'auditable_type' => Address::class,
            'auditable_id' => $address->id,
        ]);

        $address->update(['city' => 'Grodno']);

        $this->assertDatabaseHas('audits', [
            'event' => 'updated',
            'auditable_type' => Address::class,
            'auditable_id' => $address->id,
        ]);
    }
}
