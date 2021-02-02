<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Product;

class SitemapController extends Controller
{
    protected $attributesModels = [
        'categories' => 'App\Models\Category',
        'brands' => 'App\Models\Brand',
        'sizes' => 'App\Models\Size',
        'fabrics' => 'App\Models\Fabric',
        'colors' => 'App\Models\Color',
        'collections' => 'App\Models\Tag',
        'tags' => 'App\Models\Tag',
        'heels' => 'App\Models\Heel',
    ];
    //
    public function __construct()
    {
        $this->date = date('Y-m-d');
    }
    /**
     * Index
     *
     * @return Response
     */
    public function index()
    {
        return $this->render('sitemap.index', [
            'catalog1' => [
                'categories',
                'brands',
            ],
            'catalog2' => [
                'brands',
                'sizes',
                'fabrics',
                'colors',
                'tags',
                'heels',
            ],
            'catalog3' => [
                ['colors', 'sizes'],
                ['fabrics', 'sizes'],
            ],
            'date' => $this->date
        ]);
    }
    /**
     * Товары
     *
     * @return Response
     */
    public function products()
    {
        return $this->render('sitemap.products', [
            'products' => Product::with('category')->get(['id', 'slug', 'category_id']),
            'date' => $this->date
        ]);
    }
    /**
     * Категории
     *
     * @return Response
     */
    public function categories()
    {
        return $this->render('sitemap.categories', [
            'categories' => Category::get(['id', 'slug', 'path']),
            'date' => $this->date
        ]);
    }
    /**
     * Бренды
     *
     * @return Response
     */
    public function brands()
    {
        return $this->render('sitemap.brands', [
            'brands' => Brand::get(['id', 'slug']),
            'date' => $this->date
        ]);
    }
    /**
     * Категория + {еще один}
     *
     * @param string $another
     * @return Response
     */
    public function catalog2(string $another)
    {
        $model = $this->attributesModels[$another] ?? abort(404);

        return $this->render('sitemap.catalog2', [
            'categories' => Category::get(['id', 'slug', 'path']),
            'anothers' => $model::get(['id', 'slug']),
            'date' => $this->date
        ]);
    }
    /**
     * Категория + {еще один} + {еще один}
     *
     * @param string $another
     * @return Response
     */
    public function catalog3(string $another, string $another2)
    {
        $model = $this->attributesModels[$another] ?? abort(404);
        $model2 = $this->attributesModels[$another2] ?? abort(404);

        return $this->render('sitemap.catalog3', [
            'categories' => Category::get(['id', 'slug', 'path']),
            'anothers' => $model::get(['id', 'slug']),
            'anothers2' => $model2::get(['id', 'slug']),
            'date' => $this->date
        ]);
    }
    /**
     * Статика
     *
     * @return Response
     */
    public function static()
    {
        return $this->render('sitemap.static', [
            'routes' => [
                'index-page',
                'static-shops',
                'cart'
            ],
            'routesWithParams' => [
                'feedbacks' => ['reviews', 'models', 'questions'],
                'info' => ['instruction', 'payment', 'delivery', 'return', 'installments'],
            ]
        ]);
    }
    /**
     * Сформировать XML
     *
     * @param string $viewName
     * @param array $data
     * @return Response
     */
    public function render(string $viewName, array $data = []) : Response
    {
        return response()
            ->view($viewName, $data)
            ->header('Content-Type', 'text/xml');
    }
}
