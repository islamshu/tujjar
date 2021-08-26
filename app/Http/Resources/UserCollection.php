<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\City2 as City ;
use App\Http\Resources\AddressCollection;

use App\User;
use App\Address;
class UserCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                if($data->avatar_original == null){
                    $data->avatar_original = 609;
                }
                
                
                return [
                    'id' => (integer) $data->id,
                    'name' => $data->name,
                    'type' => $data->user_type,
                    'email' => $data->email,
                    // 'avatar' => $data->avatar,
                    'avatar_original' => api_asset($data->avatar_original),
                    'phone' => $data->phone,
                    'address' =>new AddressCollection(Address::where('user_id', $data->id)->get()),

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
