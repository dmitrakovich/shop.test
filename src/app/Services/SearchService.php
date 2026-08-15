<?php

namespace App\Services;

class SearchService
{
    /**
     * Search keys list from search query
     *
     * @var list<string>
     */
    protected array $searchKeys = [];

    public function __construct(string $searchQuery)
    {
        $this->searchKeys = explode(' ', $searchQuery);
    }

    /**
     * Prepare id list from search query
     *
     * @return list<int>
     */
    public function getIds(): array
    {
        $idList = array_filter($this->searchKeys, is_numeric(...));

        return array_values(array_map(intval(...), $idList));
    }

    /**
     * Use product id search
     */
    public function useSimpleSearch(): bool
    {
        return count($this->searchKeys) === 1 && is_numeric($this->searchKeys[0]);
    }
}
