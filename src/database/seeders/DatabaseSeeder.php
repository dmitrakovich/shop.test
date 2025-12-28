<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CategorySeeder::class);
        $this->call(SizeSeeder::class);
        $this->call(ColorSeeder::class);
        $this->call(FabricSeeder::class);
        $this->call(HeelSeeder::class);
        $this->call(StyleSeeder::class);
        $this->call(SeasonSeeder::class);
        $this->call(TagSeeder::class);
        $this->call(CollectionSeeder::class);
        $this->call(BrandSeeder::class);
        $this->call(ManufacturerSeeder::class);
        $this->call(UrlsSeeder::class);

        $this->call(CountriesSeeder::class);

        // admin panel
        Artisan::call('admin:install');
        $this->call(AdminPanelSeeder::class);

        // auto-generated
        $this->call(StatusesTableSeeder::class);
        $this->call(ConfigsTableSeeder::class);
        $this->call(UserGroupsTableSeeder::class);
        $this->call(StocksTableSeeder::class);
        $this->call(TagGroupsTableSeeder::class);
    }
}
