<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class ColorResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'code' => $this->code
        ];
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
