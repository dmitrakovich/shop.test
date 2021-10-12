<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $captchaScore = $request->input('captcha_score') ?? 0;

        Feedback::create([
            'user_id' => Auth::user() ? Auth::id() : 0,
            'user_name' => $request->input('name'),
            'user_email' => 'INFO@MODNY.BY',
            'text' => $request->input('text'),
            'rating' => 5,
            'product_id' => 977,
            'type_id' => $captchaScore > 4 ? Feedback::TYPE_REVIEW : Feedback::TYPE_SPAM,
            'captcha_score' => $captchaScore,
            'view_only_posted' => true,
            'publish' => false,
            'ip' => $request->ip()
        ]);

        return 'После модерации Ваш отзыв будет опубликован на сайте.';
    }
}
