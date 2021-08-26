<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class BusinessSettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'type' => $this->type,
            'value' => $this->type == 'verification_form' ? json_decode($this->value) : $this->value
        ];
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
