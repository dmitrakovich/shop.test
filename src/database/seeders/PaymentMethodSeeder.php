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
        ['name' => 'При получении', 'class' => 'COD', 'active' => true],
        ['name' => 'Банковской картой', 'class' => 'Card', 'active' => true],
        ['name' => 'Ерип', 'class' => 'ERIP', 'active' => true],
        ['name' => 'Оформить рассрочку', 'class' => 'Installment', 'active' => true],
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
