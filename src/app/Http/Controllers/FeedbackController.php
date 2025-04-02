<?php

namespace App\Http\Controllers;

use App\Data\Feedback\FeedbackData;
use App\Enums\Feedback\FeedbackType;
use App\Libraries\Seo\Facades\SeoFacade;
use App\Services\FeedbackService;
use App\Services\GoogleTagManagerService;
use Illuminate\Contracts\View\View;
use Spatie\GoogleTagManager\GoogleTagManagerFacade;

class FeedbackController extends Controller
{
    /**
     * ProductController constructor.
     */
    public function __construct(private FeedbackService $feedbackService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(GoogleTagManagerService $gtmService): View
    {
        $feedbacks = $this->feedbackService->getByType(FeedbackType::REVIEW);

        $gtmService->setViewForOther();
        SeoFacade::setTitle('Отзывы');

        return view('feedbacks-page', compact('feedbacks'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FeedbackData $feedbackData): array
    {
        $this->feedbackService->store($feedbackData);

        GoogleTagManagerFacade::user('userReview');

        return [
            'result' => 'После модерации Ваш отзыв будет опубликован на сайте.',
            'dataLayer' => GoogleTagManagerFacade::getFlashData(),
        ];
    }
}
