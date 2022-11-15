<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    protected string $tableName = 'users';

    protected string $oldTableName = 'cyizj_jshopping_users';

    protected string $oldExtTableName = 'cyizj_users';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table($this->tableName)->truncate();

        $oldUsers = DB::connection('old_mysql')
            ->table($this->oldTableName)
            ->leftJoin($this->oldExtTableName, "$this->oldTableName.user_id", '=', "$this->oldExtTableName.id")
            ->get([
                $this->oldTableName . '.user_id as id',
                $this->oldTableName . '.email',
                $this->oldTableName . '.f_name as first_name',
                $this->oldTableName . '.l_name as last_name',
                $this->oldTableName . '.m_name as patronymic_name',
                $this->oldTableName . '.mobil_phone as phone',
                // 'birthday as birth_date', // везде пусто
                // 'country_id',
                $this->oldTableName . '.street as address',
                $this->oldExtTableName . '.password',
                $this->oldExtTableName . '.registerDate as created_at',
                $this->oldExtTableName . '.lastvisitDate as updated_at',
            ]);

        foreach ($oldUsers as $oldUser) {
            if (empty($oldUser->email) || empty($oldUser->password)) {
                continue;
            }
            if ($oldUser->updated_at == '0000-00-00 00:00:00') {
                $oldUser->updated_at = null;
            }

            DB::table($this->tableName)->insert((array) $oldUser);
        }
    }
}
