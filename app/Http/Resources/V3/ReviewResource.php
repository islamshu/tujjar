<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'user' => [
                'name' => @$this->user->name
            ],
            'rating' => $this->rating,
            'comment' => $this->comment,
            'time' => $this->created_at->diffForHumans()
        ];
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
