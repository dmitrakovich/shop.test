<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FabricSeeder extends Seeder
{
    protected $tableName = 'fabrics';
    protected $values = [
        ['name' => 'натуральная кожа', 'slug' => 'leather', 'seo' => 'кожаная,кожаное,кожаные,натуральной кожи'],
        ['name' => 'лакированная кожа', 'slug' => 'lak', 'seo' => 'кожаная,кожаное,кожаные,лакированной кожи'],
        ['name' => 'натуральная замша', 'slug' => 'suede', 'seo' => 'замшевая,замшевое,замшевые,натуральной замши'],
        ['name' => 'экокожа', 'slug' => 'eco', 'seo' => 'кожаная,кожаное,кожаные,экокожи'],
        ['name' => 'искусственная кожа', 'slug' => 'imitation', 'seo' => 'кожаная,кожаное,кожаные,искусственной кожи'],
        ['name' => 'текстиль', 'slug' => 'textile', 'seo' => 'текстильная,текстильное,текстильные,текстиля'],
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
