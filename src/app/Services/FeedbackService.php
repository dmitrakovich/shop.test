<?php

namespace App\Services;

use App\Events\ReviewPosted;
use App\Models\Feedback;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;

class FeedbackService
{
    /**
     * FeedbackService constructor.
     */
    public function __construct(private Feedback $feedback) {}

    /**
     * Get feedbacks for specific product
     *
     * @return Collection<Feedback>
     */
    public function getForProduct(int $productId): Collection
    {
        return $this->feedback->newQuery()
            ->with(['answers', 'media', 'product'])
            ->where('publish', true)
            ->where('product_id', $productId)
            ->latest()
            ->get();
    }

    /**
     * Get feedbacks by type
     *
     * @return Paginator|Feedback[]
     */
    public function getByType(string $type): Paginator
    {
        return $this->feedback->newQuery()
            ->with(['answers', 'media', 'product'])
            ->where('publish', true)
            ->latest()
            ->type($type)
            ->simplePaginate(50);
    }

    /**
     * Save new feedback
     */
    public function store(array $data): void
    {
        /** @var Feedback $feedback */
        $feedback = $this->feedback->newQuery()->create($data);

        /** @var \Illuminate\Http\UploadedFile $photo */
        foreach (($data['photos'] ?? []) as $photo) {
            $feedback->addMedia($photo)->toMediaCollection('photos');
        }
        /** @var \Illuminate\Http\UploadedFile $video */
        foreach (($data['videos'] ?? []) as $video) {
            $feedback->addMedia($video)->toMediaCollection('videos');
        }

        event(new ReviewPosted(auth()->user()));
    }
}
