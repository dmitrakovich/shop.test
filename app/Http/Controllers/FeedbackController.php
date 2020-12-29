<?php

namespace App\Http\Controllers;

use App\Facades\Cart;
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
            ->latest()
            ->type($type)
            ->paginate(50);

        return view('feedbacks', compact('type', 'feedbacks'));
    }
}
