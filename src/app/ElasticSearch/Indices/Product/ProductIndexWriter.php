<?php

namespace App\ElasticSearch\Indices\Product;

/**
 * Bulk write operations for the product Elasticsearch index.
 */
class ProductIndexWriter
{
    /**
     * @param  ProductIndex  $index  Product index lifecycle helper
     */
    public function __construct(
        private readonly ProductIndex $index,
    ) {}

    /**
     * Bulk index documents.
     *
     * @param  list<array<string, mixed>>  $documents  Documents with id key
     * @return array<string, mixed> Bulk response
     */
    public function bulkIndex(array $documents): array
    {
        if ($documents === []) {
            return [];
        }

        $body = [];
        foreach ($documents as $doc) {
            if (empty($doc['id'])) {
                continue;
            }
            $body[] = ['index' => [
                '_index' => $this->index->name(),
                '_id' => (string)$doc['id'],
            ]];
            $body[] = $doc;
        }

        if ($body === []) {
            return [];
        }

        $resp = $this->index->client()->bulk(['body' => $body, 'refresh' => false]);

        return method_exists($resp, 'asArray') ? $resp->asArray() : (array)$resp;
    }

    /**
     * Bulk delete documents by id.
     *
     * @param  list<int>  $ids  Document ids
     * @return array<string, mixed> Bulk response
     */
    public function bulkDelete(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $body = [];
        foreach ($ids as $id) {
            $body[] = ['delete' => [
                '_index' => $this->index->name(),
                '_id' => (string)$id,
            ]];
        }

        $resp = $this->index->client()->bulk(['body' => $body, 'refresh' => false]);

        return method_exists($resp, 'asArray') ? $resp->asArray() : (array)$resp;
    }
}
