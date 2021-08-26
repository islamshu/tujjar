<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        $addresses['data'] = [];
        if ($this->addresses()->count() > 0) {
            $addresses['data'] = AddressResource::collection($this->addresses);
        }
        return [
            'id' => (integer) $this->id,
            'name' => $this->name,
            'type' => $this->user_type,
            'email' => $this->email,
            'avatar_original' => api_asset($this->avatar_original),
            'phone' => $this->phone,
            'address' =>$addresses,
        ];
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
