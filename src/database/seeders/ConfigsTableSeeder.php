<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfigsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        DB::table('configs')->delete();

        DB::table('configs')->insert(array (
            0 =>
            array (
                'key' => 'feedback',
                'config' => '{"discount": {"BYN": "10", "KZT": "1500", "RUB": "350", "USD": "5"}, "send_after": "72"}',
                'created_at' => '2023-01-22 21:25:55',
                'updated_at' => '2023-03-04 19:11:36',
            ),
            1 =>
            array (
                'key' => 'installment',
                'config' => '{"min_price": "100.00"}',
                'created_at' => '2023-01-11 22:00:36',
                'updated_at' => '2023-01-12 08:19:26',
            ),
            2 =>
            array (
                'key' => 'inventory_blacklist',
                'config' => '{"categories": ["Professional Anticolor", "Водоотталк. пропитка", "Восстановитель цвета для замши и нубука нейтральный", "Восстановитель цвета для замши и нубука черный", "Гелевые запяточники", "Гелевые подпяточники", "Гелевые полустельки", "Дезодорант", "Кожаные запяточники", "Кожаные подпяточники", "Кожаные полустельки", "Краска-аэрозоль", "Краска-восстановитель цвета", "Крем", "Стельки", "Обувная щетка", "Сертификат", "Крем-гель", "Рожок", "Шнурки"]}',
                'created_at' => '2023-06-09 10:16:52',
                'updated_at' => '2023-06-21 22:11:42',
            ),
            3 =>
            array (
                'key' => 'newsletter_register',
                'config' => '{"active": true, "to_days": 30, "from_days": 5}',
                'created_at' => '2023-03-30 10:01:12',
                'updated_at' => '2023-03-30 10:01:12',
            ),
            4 =>
            array (
                'key' => 'rating',
                'config' => '{"algoritm": {"new": {"hit": "0", "sale": "0", "photo": "1", "price": "5", "trand": "120", "action": "10", "season": "30", "aviable": "10", "newless": "90", "popular": "80", "category": "50", "discount": "0", "purshase": "150"}, "sale": {"hit": "15", "sale": "50", "photo": "1", "price": "3", "trand": "150", "action": "10", "season": "10", "aviable": "20", "newless": "5", "popular": "100", "category": "20", "discount": "50", "purshase": "200"}, "action": {"hit": "100", "sale": "50", "photo": "1", "price": "10", "trand": "30", "action": "3", "season": "-15", "aviable": "15", "newless": "10", "popular": "50", "category": "1", "discount": "5", "purshase": "15"}}, "basic_summ": {"hit": {"summ": 2100, "segment": 0.003498259378400894}, "sale": {"summ": 0, "segment": 0}, "photo": {"summ": 160350, "segment": 0.26711709110789683}, "price": {"summ": 59483.386282937776, "segment": 0.09908967329682387}, "trand": {"summ": 6812.121212121174, "segment": 0.011347889008145982}, "action": {"summ": 800, "segment": 0.0013326702393908168}, "season": {"summ": 53400, "segment": 0.08895573847933702}, "aviable": {"summ": 77600, "segment": 0.12926901322090922}, "newless": {"summ": 11138.669201959956, "segment": 0.018555216189838863}, "popular": {"summ": 8462.676822633312, "segment": 0.014097446933882317}, "category": {"summ": 158400, "segment": 0.26386870739938173}, "discount": {"summ": 55905.53337874567, "segment": 0.09312955068890535}, "purshase": {"summ": 5846.153846153817, "segment": 0.009738744057086688}}, "cur_season": "2", "last_update": "2023-08-14 17:15:07", "algoritm_name": {"new": "Новинки", "sale": "Распродажа", "action": "Акция"}, "curr_algoritm": "sale", "parametr_name": {"hit": "Хиты", "sale": "Ликвидация", "photo": "Наличие фотографий", "price": "Цена", "trand": "Корзина", "action": "На акции", "season": "Сезонность", "aviable": "Наличие размеров", "newless": "Новизна", "popular": "Просмотры", "category": "Исключенные категории", "discount": "Скидка", "purshase": "Продажи"}, "false_category": "25,26,27,28"}',
                'created_at' => '2022-10-26 17:05:02',
                'updated_at' => '2023-08-14 17:15:07',
            ),
            5 =>
            array (
                'key' => 'sms',
                'config' => '{"enabled": "on"}',
                'created_at' => '2023-03-20 23:16:58',
                'updated_at' => '2023-03-20 23:17:00',
            ),
        ));


    }
}
