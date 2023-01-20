<?php

namespace App\Http\Controllers;

use App\Events\ReviewPosted;
use App\Http\Requests\FeedbackRequest;
use App\Libraries\Seo\Facades\SeoFacade;
use App\Models\Feedback;
use App\Services\GoogleTagManagerService;
use Spatie\GoogleTagManager\GoogleTagManagerFacade;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(GoogleTagManagerService $gtmService, ?string $type = null)
    {
        $type = Feedback::getType($type);

        $feedbacks = Feedback::with(['answers', 'media', 'product'])
            ->where('publish', true)
            ->latest()
            ->type($type)
            ->paginate(50);

        $gtmService->setViewForOther();
        SeoFacade::setTitle('Отзывы');

        return view('feedbacks-page', compact('type', 'feedbacks'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\FeedbackRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(FeedbackRequest $feedbackRequest)
    {
        $data = $feedbackRequest->validated();
        /** @var Feedback $feedback */
        $feedback = Feedback::create($data);

        /** @var \Illuminate\Http\UploadedFile $photo */
        foreach (($data['photos'] ?? []) as $photo) {
            $feedback->addMedia($photo)->toMediaCollection('photos');
        }
        /** @var \Illuminate\Http\UploadedFile $photo */
        foreach (($data['videos'] ?? []) as $video) {
            $feedback->addMedia($video)->toMediaCollection('videos');
        }

        event(new ReviewPosted(auth()->user()));

        GoogleTagManagerFacade::user('userReview');

        return [
            'result' => 'После модерации Ваш отзыв будет опубликован на сайте.',
            'dataLayer' => GoogleTagManagerFacade::getFlashData(),
        ];
    }
}
