<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\TruncatesTables;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeasonSeeder extends Seeder
{
    use TruncatesTables;

    protected $tableName = 'seasons';

    protected $values = [
        ['name' => 'зима', 'slug' => 'winter', 'seo' => 'зимний,зимняя,зимнее,зимние'],
        ['name' => 'лето', 'slug' => 'summer', 'seo' => 'летний,летняя,летнее,летние'],
        ['name' => 'демисезон', 'slug' => 'demi', 'seo' => 'демисезонный,демисезонная,демисезонное,демисезонные'],
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
