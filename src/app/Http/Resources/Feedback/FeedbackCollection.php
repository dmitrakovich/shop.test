<?php

namespace App\Http\Resources\Feedback;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use LogicException;

/**
 * Paginated reviews aligned with catalog products: wraps LengthAwarePaginator (current_page, data, links, total, …).
 */
class FeedbackCollection extends ResourceCollection
{
    /**
     * @var class-string<FeedbackResource>
     */
    public $collects = FeedbackResource::class;

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        if (!$this->resource instanceof LengthAwarePaginator) {
            throw new LogicException(self::class . ' expects a LengthAwarePaginator.');
        }

        return $this->resource->toArray();
    }
}
