<?php

namespace App\Http\Requests;

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
        $session = $this->getSession();
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
        unset($slugs[0]); // catalog

        if (!empty($slugs)) {
            $filtersTemp = Url::whereIn('slug', $slugs)
                ->with('filters') // :id,name !!!
                ->get(['slug', 'model_type', 'model_id']);

            foreach ($filtersTemp as $value) {
                $filters[$value->model_type][$value->slug] = $value->toArray();
            }
            // говнокод на скорую руку для сортировки категорий в правильном порядке
            if (isset($filters['App\Models\Category'])) {
                $categoriesFilters = $filters['App\Models\Category'];
                $filters['App\Models\Category'] = [];
                foreach ($slugs as $slug) {
                    if (isset($categoriesFilters[$slug])) {
                        $filters['App\Models\Category'][$slug] = $categoriesFilters[$slug];
                    }
                }
            }
        }

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
