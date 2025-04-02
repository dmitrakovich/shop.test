<?php

namespace App\Services;

use App\Data\Feedback\FeedbackData;
use App\Enums\Feedback\FeedbackType;
use App\Events\ReviewPosted;
use App\Models\Feedback;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

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
    public function getByType(FeedbackType $type): Paginator
    {
        return $this->feedback->newQuery()
            ->with(['answers', 'media', 'product'])
            ->where('publish', true)
            ->latest()
            ->where('type', $type)
            ->simplePaginate(50);
    }

    /**
     * Save new feedback
     */
    public function store(FeedbackData $feedbackData): void
    {
        /** @var Feedback $feedback */
        $feedback = $this->feedback->newQuery()->create($feedbackData->toArray());

        foreach ($feedbackData->photos as $photo) {
            $feedback->addMedia($photo)->toMediaCollection('photos');
        }
        foreach ($feedbackData->videos as $video) {
            $feedback->addMedia($video)->toMediaCollection('videos');
        }

        event(new ReviewPosted(Auth::user()));
    }
}
