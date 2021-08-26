<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\City2;
class AddressCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                return [
                    'id'      => $data->id,
                    'user_id' => $data->user_id,
                    'address' => $data->address,
                    'country' => $data->country,
                    'governorate_ar' => City2::find($data->city)->name,
                    'governorate_en' => City2::find($data->city)->name_en,
                   
                    'state_ar' =>  City2::find($data->postal_code)->name,
                    'state_en' =>  City2::find($data->postal_code)->name_en,
                    'phone' => $data->phone,
                    'set_default' => $data->set_default
                ];
            })
        ];
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
