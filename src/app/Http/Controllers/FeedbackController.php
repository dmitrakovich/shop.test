<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeedbackRequest;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(?string $type = null)
    {
        $type = Feedback::getType($type);

        $feedbacks = Feedback::with(['answers', 'media'])
            ->where('publish', true)
            ->latest()
            ->type($type)
            ->paginate(50);

        return view('feedbacks', compact('type', 'feedbacks'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\FeedbackRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(FeedbackRequest $feedbackRequest)
    {
        $data = $feedbackRequest->validated();
        $feedback = Feedback::create($data);

        foreach (($data['photos'] ?? []) as $photo) {
            if ($photo->getSize() > Feedback::MAX_PHOTO_SIZE) {
                continue;
            }
            $feedback->addMedia($photo->getPathname())->toMediaCollection();
        }

        // foreach (($data['videos'] ?? []) as $video) {
        //     if ($video->getSize() > Feedback::MAX_VIDEO_SIZE) {
        //         continue;
        //     }
        //     $feedback->addMedia($video->getPathname())->toMediaCollection();
        // }

        return 'После модерации Ваш отзыв будет опубликован на сайте.';
    }
}
