<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
class CategoryCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function($data) {
                $a =  [
                    'id'=>$data->id,
                    'name_ar' => $data->name,
                    'name_en' => $data->name_en,
                    'banner' => api_asset($data->banner),
                    'icon' => api_asset($data->icon),
                    // 'brands' => brandsOfCategory($data->id),
                    'links' => [
                        'products' => route('api.products.category', $data->id),
                    ]
                   
                   
                ];
                if($data->categories()->count() > 0){
                    $a['sub-cat'] = new CategoryCollection($data->categories);
                }
                return $a;
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
