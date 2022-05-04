<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    /**
     * Searck keys list from search query
     *
     * @var array
     */
    protected $searchKeys = [];

    public function __construct(string $searchQuery)
    {
        $this->searchKeys = explode(' ', $searchQuery);
    }

    /**
     * Generate search query
     *
     * @param Builder $query
     * @param string $column
     * @param array $search
     * @return Builder
     */
    public function generateSearchQuery(Builder $query, string $column)
    {
        $value = reset($this->searchKeys);
        $query->where($column, 'like', "%$value%");

        while ($value = next($this->searchKeys)) {
            $query->orWhere($column, 'like', "%$value%");
        }
        return $query;
    }

    /**
     * Prepare id list from serach query
     *
     * @return array
     */
    public function getIds(): array
    {
        $idList = array_filter($this->searchKeys, function($value) {
            return is_numeric($value);
        });
        return array_values(array_map('intval', $idList));
    }

    /**
     * Use product id search
     *
     * @return boolean
     */
    public function useSimpleSearch(): bool
    {
        return count($this->searchKeys) === 1 && is_numeric($this->searchKeys[0]);
    }
}
