<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\TruncatesTables;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    use TruncatesTables;

    protected $tableName = 'tags';

    protected $values = [
        ['name' => 'лодочки', 'slug' => 'pumps', 'seo' => 'лодочки'],
        ['name' => 'стразы', 'slug' => 'straz', 'seo' => 'со стразами'],
        ['name' => 'еврозима', 'slug' => 'eurowinter', 'seo' => 'еврозима'],
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
