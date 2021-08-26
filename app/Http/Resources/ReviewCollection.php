<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\User;
class ReviewCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                
                return [
                    'user' => [
                        'name' => $this->getUser($data->user_id)
                    ],
                    'rating' => $data->rating,
                    'comment' => $data->comment,
                    'time' => $data->created_at->diffForHumans()
                ];
            })
        ];
    }
    protected function getUser($data){
        $user = User::find($data);
        if($user){
            $user=User::find($data)->name;
        }else
        {
            $user=null;
        }
                return $user;

        }
    

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
