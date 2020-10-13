<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->truncate();

        $importDataFile = database_path() . '/import/cyizj_users.php';
        if (file_exists($importDataFile)) {
            require_once $importDataFile;
            if (isset($cyizj_users) && is_array($cyizj_users)) {
                foreach ($cyizj_users as $user) {
                    if (empty($user['email']) || empty($user['password'])) {
                        continue;
                    }
                    $data[$user['email']] = [
                        'first_name' => $user['name'] ?? '',
                        'email' => $user['email'],
                        'email_verified_at' => now(),
                        'password' => $user['password'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        DB::table('users')->insert($data);
    }
}
