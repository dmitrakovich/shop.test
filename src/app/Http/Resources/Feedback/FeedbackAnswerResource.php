<?php

namespace App\Http\Resources\Feedback;

use App\Models\FeedbackAnswer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FeedbackAnswer
 */
class FeedbackAnswerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_type' => $this->user_type,
            'user_id' => $this->user_id,
            'text' => $this->text,
            'created_at' => $this->created_at->format('d.m.Y'),
        ];
    }
}
