<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeedbackRequest;
use App\Libraries\Seo\Facades\SeoFacade;
use App\Models\Feedback;
use App\Services\FeedbackService;
use App\Services\GoogleTagManagerService;
use Spatie\GoogleTagManager\GoogleTagManagerFacade;

class FeedbackController extends Controller
{
    /**
     * ProductController constructor.
     */
    public function __construct(private FeedbackService $feedbackService) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(GoogleTagManagerService $gtmService, ?string $type = null)
    {
        $type = Feedback::getType($type);
        $feedbacks = $this->feedbackService->getByType($type);

        $gtmService->setViewForOther();
        SeoFacade::setTitle('Отзывы');

        return view('feedbacks-page', compact('type', 'feedbacks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FeedbackRequest $feedbackRequest): array
    {
        $this->feedbackService->store($feedbackRequest->validated());

        GoogleTagManagerFacade::user('userReview');

        return [
            'result' => 'После модерации Ваш отзыв будет опубликован на сайте.',
            'dataLayer' => GoogleTagManagerFacade::getFlashData(),
        ];
    }
}
