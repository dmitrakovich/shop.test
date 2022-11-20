<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryMethodSeeder extends Seeder
{
    protected $tableName = 'delivery_methods';

    protected $values = [
        ['name' => 'Курьером с примеркой', 'class' => 'BelpostCourierFitting', 'active' => true],
        ['name' => 'Курьер', 'class' => 'BelpostCourier', 'active' => true],
        ['name' => 'Белпочта', 'class' => 'Belpost', 'active' => true],
        ['name' => 'Емс', 'class' => 'BelpostEMS', 'active' => true],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table($this->tableName)->truncate();

        foreach ($this->values as $value) {
            DB::table($this->tableName)->insert($value);
        }
    }
}
