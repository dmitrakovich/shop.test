<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\User\User
 */
class UserResource extends JsonResource
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
            'group_id' => $this->group_id,
            'discount_card_number' => $this->discount_card_number,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'patronymic_name' => $this->patronymic_name,
            'phone' => $this->phone,
            'birth_date' => $this->birth_date->format('Y-m-d'),
            'address' => new UserAddressResource($this->lastAddress),
        ];
    }
}
