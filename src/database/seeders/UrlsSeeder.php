<?php

namespace Database\Seeders;

use App\Models\ProductAttributes\Status;
use App\Models\Url;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UrlsSeeder extends Seeder
{
    protected $slugs = null;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('urls')->truncate();

        $this->setSlugs('App\Models\Category');
        $this->setSlugs('App\Models\Product', true);

        $this->setSlugs('App\Models\Color');
        $this->setSlugs('App\Models\Size');
        $this->setSlugs('App\Models\Brand');
        $this->setSlugs('App\Models\Season');
        $this->setSlugs('App\Models\Fabric');
        $this->setSlugs('App\Models\Heel');
        $this->setSlugs('App\Models\Tag');
        $this->setSlugs('App\Models\Style');
        $this->setSlugs('App\Models\Collection');
        $this->setSlugs(Status::class);

        foreach ($this->getSlugs() as $slug) {
            $slug = new Url($slug);
            $slug->save();
        }
    }

    private function setSlugs(string $class, bool $withTrashed = false)
    {
        $slugs = (new $class)
            ->when($withTrashed, fn ($query) => $query->withTrashed())
            ->get(['id', 'slug']);

        foreach ($slugs as $slug) {
            $this->slugs[] = [
                'slug' => $slug['slug'],
                'model_id' => $slug['id'],
                'model_type' => $class,
            ];
        }
    }

    private function getSlugs()
    {
        return $this->slugs ?? [];
    }
}
