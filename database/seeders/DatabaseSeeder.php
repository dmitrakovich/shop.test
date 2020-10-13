<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(SizeSeeder::class);
        $this->call(ColorSeeder::class);        
        $this->call(FabricSeeder::class);
        $this->call(HeelSeeder::class);
        $this->call(StyleSeeder::class);
        $this->call(SeasonSeeder::class);
        $this->call(TagSeeder::class);
        $this->call(BrandSeeder::class);

        $this->call(ProductSeeder::class);
        $this->call(ProductImageSeeder::class);
        $this->call(UrlsSeeder::class);

        // admin panel
        Artisan::call('admin:install');
        $this->call(AdminPanelSeeder::class);
    }
}
