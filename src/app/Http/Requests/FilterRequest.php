<?php

namespace App\Http\Requests;

use App\Models\City;
use App\Models\Product;
use App\Models\ProductAttributes\Top;
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
    public function getSorting(): string
    {
        $sorting = $this->input('sort');

        if ($this->hasSession()) {
            $sessionSorting = $this->session()->get('sorting');
            if ($sorting && $sorting !== $sessionSorting) {
                $this->session()->put('sorting', $sorting);
            }
        }

        return $sorting ?? Product::DEFAULT_SORT;
    }

    /**
     * Get filters
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

        $this->addTopProducts($filters);

        return $filters;
    }

    /**
     * Get city
     */
    public function getCity(): ?City
    {
        $citySlug = $this->route('city');

        return $citySlug ? City::query()->where('slug', $citySlug)->first() : null;
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

    /**
     * Add Top filters models to filters if exist
     */
    protected function addTopProducts(array &$filters): void
    {
        $top = $this->input('top', '');
        $top = array_filter(explode(',', $top), 'is_numeric');

        if (!empty($top)) {
            $filters[Top::class] = array_map(function (int $id) {
                $urlModel = new Url([
                    'slug' => 'top',
                    'model_type' => Top::class,
                    'model_id' => $id,
                ]);

                return $urlModel->setRelation('filters', new Top());
            }, $top);
        }
    }
}
