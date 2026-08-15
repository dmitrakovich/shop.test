<?php

namespace App\Http\Requests;

use App\Enums\Product\ProductSort;
use App\Models\Url;
use App\Services\FilterService;
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
     */
    public function getSorting(): ProductSort
    {
        $sorting = $this->input('sort');

        return ProductSort::fromRequest(is_string($sorting) ? $sorting : null);
    }

    /**
     * Get filters
     *
     * @return array<string, array<string, Url>>
     */
    public function getFilters(): array
    {
        $slugs = array_filter(explode('/', 'catalog/' . $this->path));
        $filters = $this->getStaticFilters($slugs);

        Url::query()->whereIn('slug', $slugs)
            ->with('filters')
            ->get(['slug', 'model_type', 'model_id'])
            ->sortBy(fn (Url $url) => array_search($url->slug, $slugs))
            ->each(function (Url $url) use (&$filters) {
                $filters[$url->model_type][$url->slug] = $url;
            });

        return $filters;
    }

    /**
     * Get static filters (not from db)
     */
    public function getStaticFilters(array &$slugs): array
    {
        $filters = [];
        /** @var FilterService $filterService */
        $filterService = app(FilterService::class);
        foreach ($slugs as $key => $slug) {
            /** @var Url $url */
            if ($url = $filterService->getStaticFilter($slug)) {
                $filters[$url->model_type][$url->slug] = $url;
                unset($slugs[$key]);
            }
        }

        return $filters;
    }
}
