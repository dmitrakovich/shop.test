<?php

namespace App\Http\Resources\Feedback;

use App\Http\Resources\Product\CatalogProductResource;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Feedback
 */
class FeedbackResource extends JsonResource
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
            'user_name' => $this->user_name,
            'user_city' => $this->user_city,
            'text' => $this->text,
            'rating' => $this->rating,
            'created_at' => $this->created_at,

            'answers' => FeedbackAnswerResource::collection($this->answers),
            'product' => new CatalogProductResource($this->product),
            // 'media' => $this->media,
        ];
    }
}
