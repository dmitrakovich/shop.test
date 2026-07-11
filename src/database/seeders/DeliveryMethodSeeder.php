<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\TruncatesTables;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryMethodSeeder extends Seeder
{
    use TruncatesTables;

    protected $tableName = 'delivery_methods';

    protected $values = [
        ['name' => 'Курьером с примеркой', 'instance' => 'BelpostCourierFitting', 'active' => true],
        ['name' => 'Курьер', 'instance' => 'BelpostCourier', 'active' => true],
        ['name' => 'Белпочта', 'instance' => 'Belpost', 'active' => true],
        ['name' => 'Емс', 'instance' => 'BelpostEMS', 'active' => true],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->truncateTable($this->tableName);

        foreach ($this->values as $value) {
            DB::table($this->tableName)->insert($value);
        }
    }
}
