<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

abstract class AbstractSeeder extends Seeder
{
    protected $useTimestamps = true;
    protected $tableName;
    protected $values;

    /**
     * Check required params
     *
     * @return void
     */
    protected function checkRequireParams()
    {
        if (empty($this->tableName)) {
            throw new \Exception('Empty table name!');
        }
        if (!is_string($this->tableName)) {
            throw new \Exception('Table name not string!');
        }
        if (empty($this->values)) {
            throw new \Exception('Empty values!');
        }
        if (!is_array($this->values)) {
            throw new \Exception('Values not array!');
        }
    }

    /**
     * Set timestamps values
     *
     * @return void
     */
    protected function setTimestamps()
    {
        if (!$this->useTimestamps) {
           return;
        }
        $now = now();
        foreach ($this->values as &$value) {
            $value['created_at'] = $now;
        }
    }

    /**
     * Prepare values before inserting
     *
     * @return void
     */
    public function prepareValues()
    {
        //
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->checkRequireParams();
        $this->setTimestamps();
        $this->prepareValues();

        DB::table($this->tableName)->truncate();

        foreach ($this->values as $value) {
            DB::table($this->tableName)->insert($value);
        }
    }
}
