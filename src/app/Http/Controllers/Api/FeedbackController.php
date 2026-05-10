<?php

namespace App\Http\Controllers\Api;

use App\Data\Feedback\FeedbackData;
use App\Enums\Feedback\FeedbackType;
use App\Http\Controllers\Controller;
use App\Http\Resources\Ads\BannerResource;
use App\Http\Resources\Feedback\FeedbackCollection;
use App\Models\Feedback;
use App\Repositories\BannerRepository;
use App\Services\FeedbackService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedbackController extends Controller
{
    public function __construct(
        private readonly FeedbackService $feedbackService,
        private readonly BannerRepository $bannerRepository,
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return array{feedbacks: FeedbackCollection, banners: AnonymousResourceCollection}
     */
    public function index(): array
    {
        return [
            'feedbacks' => new FeedbackCollection($this->feedbackService->getByType(FeedbackType::REVIEW)),
            'banners' => BannerResource::collection($this->bannerRepository->getFeedbackBanners()),
        ];
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
