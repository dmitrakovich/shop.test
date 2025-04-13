<?php

namespace App\Http\Controllers\Api;

use App\Data\Feedback\FeedbackData;
use App\Enums\Feedback\FeedbackType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Feedback\FeedbackResource;
use App\Models\Feedback;
use App\Services\FeedbackService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedbackController extends Controller
{
    public function __construct(private FeedbackService $feedbackService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        return FeedbackResource::collection($this->feedbackService->getByType(FeedbackType::REVIEW));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FeedbackData $feedbackData): void
    {
        $this->feedbackService->store($feedbackData);
    }

    public function storeAnswer(Feedback $feedback): void
    {
        // code...
    }
}
