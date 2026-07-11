<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\TruncatesTables;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentMethodSeeder extends Seeder
{
    use TruncatesTables;

    protected $tableName = 'payment_methods';

    protected $values = [
        ['name' => 'При получении', 'instance' => 'COD', 'active' => true],
        ['name' => 'Банковской картой', 'instance' => 'Card', 'active' => true],
        ['name' => 'Ерип', 'instance' => 'ERIP', 'active' => true],
        ['name' => 'Оформить рассрочку', 'instance' => 'Installment', 'active' => true],
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
