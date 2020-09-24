<?php

use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
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
        DB::table($this->tableName)->truncate();

        foreach ($this->values as $value) {
            DB::table($this->tableName)->insert($value);
        }
    }
}
