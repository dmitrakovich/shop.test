<?php

namespace App\Observers;

use App\Models\Feedback;
use Illuminate\Support\Facades\Cache;

class FeedbackObserver
{
    /**
     * Reset the review-discount cache when feedback is created or updated in admin/API.
     */
    public function saved(Feedback $feedback): void
    {
        $this->forgetUserCache($feedback->user_id);

        if ($feedback->wasChanged('user_id')) {
            $this->forgetUserCache($feedback->getOriginal('user_id'));
        }
    }

    /**
     * Reset the review-discount cache when feedback is deleted (including soft deletes).
     */
    public function deleted(Feedback $feedback): void
    {
        $this->forgetUserCache($feedback->user_id);
    }

    /**
     * Reset the review-discount cache when soft-deleted feedback is restored.
     */
    public function restored(Feedback $feedback): void
    {
        $this->forgetUserCache($feedback->user_id);
    }

    private function forgetUserCache(mixed $userId): void
    {
        if (!$userId) {
            return;
        }

        Cache::forget('user-' . (int)$userId);
    }
}
