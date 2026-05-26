<?php

namespace Tests\Unit\Rules\User;

use App\Rules\User\UniqueUserPhone;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UniqueUserPhoneTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');

        DB::purge('sqlite');
        DB::reconnect('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('phone')->unique();
        });

        DB::table('users')->insert([
            'id' => 1,
            'phone' => 375291234567,
        ]);
    }

    public function test_it_rejects_phone_owned_by_another_user(): void
    {
        $validator = Validator::make([
            'phone' => '+375 (29) 123-45-67',
        ], [
            'phone' => [new UniqueUserPhone()],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame('Пользователь с таким телефоном уже существует.', $validator->errors()->first('phone'));
    }

    public function test_it_allows_current_user_phone_when_ignored(): void
    {
        $validator = Validator::make([
            'phone' => '+375 (29) 123-45-67',
        ], [
            'phone' => [new UniqueUserPhone(ignoreUserId: 1)],
        ]);

        $this->assertFalse($validator->fails(), implode(' ', $validator->errors()->all()));
    }

    public function test_it_allows_unused_phone(): void
    {
        $validator = Validator::make([
            'phone' => '+375 (29) 765-43-21',
        ], [
            'phone' => [new UniqueUserPhone()],
        ]);

        $this->assertFalse($validator->fails(), implode(' ', $validator->errors()->all()));
    }

    public function test_it_rejects_invalid_phone(): void
    {
        $validator = Validator::make([
            'phone' => 'invalid-phone',
        ], [
            'phone' => [new UniqueUserPhone()],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertSame('Некорректный номер телефона.', $validator->errors()->first('phone'));
    }
}
