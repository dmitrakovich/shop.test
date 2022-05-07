<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\Url;
use App\Models\Product;
use App\Models\ProductAttributes\Top;
use Illuminate\Foundation\Http\FormRequest;

class FilterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * Get current sorting
     *
     * @return string
     */
    public function getSorting(): string
    {
        $session = $this->session();
        $sorting = $this->input('sort') ?? $session->get('sorting', Product::DEFAULT_SORT);
        if ($session->get('sorting') <> $sorting) {
            $session->put('sorting', $sorting);
        }
        return $sorting;
    }

    /**
     * Get filters
     *
     * @return array
     */
    public function getFilters(): array
    {
        $filters = [];
        $slugs = $this->path() ? explode('/', $this->path()) : [];

        $filtersTemp = Url::whereIn('slug', $slugs)
            ->with('filters')
            ->get(['slug', 'model_type', 'model_id']);

        foreach ($filtersTemp as $value) {
            $filters[$value->model_type][$value->slug] = $value;
        }

        uksort($filters[Category::class], function($a, $b) use ($slugs) {
            return array_search($a, $slugs) > array_search($b, $slugs);
        });

        $this->addTopProducts($filters);

        return $filters;
    }

    /**
     * @param array $filters
     * @return void
     */
    protected function addTopProducts(array &$filters)
    {
        $top = $this->input('top', '');
        $top = array_filter(explode(',', $top));

        if (!empty($top)) {
            $filters[Top::class] = array_map(function (int $value) {
                return ['model_id' => $value];
            }, $top);
        }
    }
}
