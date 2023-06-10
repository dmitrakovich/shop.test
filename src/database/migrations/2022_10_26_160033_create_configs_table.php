<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configs', function (Blueprint $table) {
            $table->string('key', 20)->primary();
            $table->json('config');
            $table->timestamps();
        });

        $this->createDefaultConfigs();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configs');
    }

    /**
     * Create stubs for configs
     */
    private function createDefaultConfigs(): void
    {
        $now = now();

        DB::table('configs')->insert([
            [
                'key' => 'rating',
                'config' => $this->getRatingConfig(),
                'created_at' => $now,
            ],
            [
                'key' => 'installment',
                'config' => '{"min_price": "100.00"}',
                'created_at' => $now,
            ],
            [
                'key' => 'feedback',
                'config' => '{"discount": {"BYN": "10", "KZT": "1500", "RUB": "350", "USD": "5"}, "send_after": "72"}',
                'created_at' => $now,
            ],
            [
                'key' => 'sms',
                'config' => '{"enabled": "on"}',
                'created_at' => $now,
            ],
            [
                'key' => 'newsletter_register',
                'config' => '{"active": true, "to_days": 30, "from_days": 5}',
                'created_at' => $now,
            ],
        ]);
    }

    /**
     * Rating config
     */
    private function getRatingConfig(): string
    {
        return json_encode([
            'cur_season' => '17',
            'false_category' => '20,22,38',
            'curr_algoritm' => 'new',
            'algoritm_name' => [
                'new' => 'Новинки',
                'sale' => 'Распродажа',
                'action' => 'Акция',
            ],
            'parametr_name' => [
                'popular' => 'Просмотры',
                'purshase' => 'Продажи',
                'trand' => 'Корзина',
                'newless' => 'Новизна',
                'price' => 'Цена',
                'discount' => 'Скидка',
                'photo' => 'Наличие фотографий',
                'aviable' => 'Наличие размеров',
                'season' => 'Сезонность',
                'category' => 'Исключенные категории',
                'action' => 'На акции',
                'hit' => 'Хиты',
                'sale' => 'Ликвидация',
            ],
            'algoritm' => [
                'new' => [
                    'popular' => '70',
                    'purshase' => '50',
                    'trand' => '50',
                    'newless' => '50',
                    'price' => '10',
                    'discount' => '-10',
                    'photo' => '1',
                    'aviable' => '9',
                    'season' => '15',
                    'category' => '5',
                    'action' => '2',
                    'hit' => '15',
                    'sale' => '30',
                ],
                'sale' => [
                    'popular' => '50',
                    'purshase' => '100',
                    'trand' => '70',
                    'newless' => '15',
                    'price' => '3',
                    'discount' => '5',
                    'photo' => '1',
                    'aviable' => '10',
                    'season' => '3',
                    'category' => '1',
                    'action' => '5',
                    'hit' => '100',
                    'sale' => '50',
                ],
                'action' => [
                    'popular' => '50',
                    'purshase' => '15',
                    'trand' => '30',
                    'newless' => '10',
                    'price' => '10',
                    'discount' => '5',
                    'photo' => '1',
                    'aviable' => '15',
                    'season' => '-15',
                    'category' => '1',
                    'action' => '3',
                    'hit' => '100',
                    'sale' => '50',
                ],
            ],
            'basic_summ' => [
                'newless' => [
                    'summ' => 14539.757194784675448318012058734893798828125,
                    'segment' => 0.0297313137505347373223951734644288080744445323944091796875,
                ],
                'price' => [
                    'summ' => 50232.001692047226242721080780029296875,
                    'segment' => 0.10271584199214443822167908137998892925679683685302734375,
                ],
                'discount' => [
                    'summ' => 41889.083849957401980645954608917236328125,
                    'segment' => 0.08565600360315954453493958453691448085010051727294921875,
                ],
                'season' => [
                    'summ' => 0,
                    'segment' => 0.0,
                ],
                'action' => [
                    'summ' => 0,
                    'segment' => 0.0,
                ],
                'hit' => [
                    'summ' => 36000,
                    'segment' => 0.073613835546247674557207574252970516681671142578125,
                ],
                'sale' => [
                    'summ' => 0,
                    'segment' => 0.0,
                ],
                'category' => [
                    'summ' => 149500,
                    'segment' => 0.305701900393445191728147847243235446512699127197265625,
                ],
                'photo' => [
                    'summ' => 97880,
                    'segment' => 0.2001478395351867500817633072074386291205883026123046875,
                ],
                'aviable' => [
                    'summ' => 84330,
                    'segment' => 0.17244040976708518986271201356430537998676300048828125,
                ],
                'popular' => [
                    'summ' => 6590.7380607814820905332453548908233642578125,
                    'segment' => 0.01347693077041009317162956193669742788188159465789794921875,
                ],
                'purshase' => [
                    'summ' => 3900.0,
                    'segment' => 0.0079748321841768311790676904138308600522577762603759765625,
                ],
                'trand' => [
                    'summ' => 4176.923076923061671550385653972625732421875,
                    'segment' => 0.0085410924576094747473486989974844618700444698333740234375,
                ],
            ],
            'last_update' => '2021-09-15-17:15',
        ]);
    }
};
