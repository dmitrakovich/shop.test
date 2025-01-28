<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    /**
     * Search keys list from search query
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
     */
    public function generateSearchQuery(Builder $query, string $column): Builder
    {
        $value = reset($this->searchKeys);
        $query->where($column, 'like', "%$value%");

        while ($value = next($this->searchKeys)) {
            $query->orWhere($column, 'like', "%$value%");
        }

        return $query;
    }

    /**
     * Prepare id list from search query
     */
    public function getIds(): array
    {
        $idList = array_filter($this->searchKeys, fn ($value) => is_numeric($value));

        return array_values(array_map('intval', $idList));
    }

    /**
     * Use product id search
     */
    public function useSimpleSearch(): bool
    {
        return count($this->searchKeys) === 1 && is_numeric($this->searchKeys[0]);
    }

    /**
     * Use one value for search
     */
    public function generateSimpleSearchQuery(Builder $query, array $fields): Builder
    {
        return $query->where(function (Builder $query) use ($fields) {
            $searchValue = $this->getIds()[0];
            foreach ($fields as $field) {
                $query->orWhere($field, $searchValue);
            }

            return $query;
        });
    }
}
