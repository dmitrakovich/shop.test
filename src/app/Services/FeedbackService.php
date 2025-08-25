<?php

namespace App\Services;

use App\Data\Feedback\FeedbackData;
use App\Enums\Feedback\FeedbackType;
use App\Events\ReviewPosted;
use App\Models\Feedback;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
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
            ->with([
                'answers' => fn (Builder $query) => $query->where('publish', true),
                'media',
                'product',
            ])
            ->where('publish', true)
            ->where('product_id', $productId)
            ->latest()
            ->get();
    }

    /**
     * Get feedbacks by type
     *
     * @return LengthAwarePaginator<array-key, Feedback>
     */
    public function getByType(FeedbackType $type): LengthAwarePaginator
    {
        return $this->feedback->newQuery()
            ->with([
                'answers' => fn (Builder $query) => $query->where('publish', true),
                'media',
                'product',
            ])
            ->where('publish', true)
            ->latest()
            ->where('type', $type)
            ->paginate(50)
            ->onEachSide(1);
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
