<?php

use App\Url;
use Illuminate\Database\Seeder;

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

        $this->setSlugs('App\Category');
        $this->setSlugs('App\Product');

        $this->setSlugs('App\Color');
        $this->setSlugs('App\Size', 'size-');
        $this->setSlugs('App\Brand');
        $this->setSlugs('App\Season');
        $this->setSlugs('App\Fabric');
        $this->setSlugs('App\Heel', 'heel-');
        $this->setSlugs('App\Tag');
        $this->setSlugs('App\Style');

        foreach ($this->getSlugs() as $slug) {
            $slug = new Url($slug);
            $slug->save();
        }
    }

    private function setSlugs(string $class, string $prefix = null)
    {
        $slugs = (new $class)->all(['id', 'slug']);

        foreach ($slugs as $slug) {
            $this->slugs[] = [
                'slug' => $prefix . $slug['slug'],
                'model_id' => $slug['id'],
                'model_type' => $class
            ];
        }
    }

    private function getSlugs()
    {
        return $this->slugs ?? [];
    }
}
