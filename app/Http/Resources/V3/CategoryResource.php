<?php

namespace App\Http\Resources\V3;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\V3\Category;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        $a =  [
            'id'=>$this->id,
            'name_ar' => $this->name,
            'name_en' => $this->name_en,
            'banner' => api_asset($this->banner),
            'icon' => api_asset($this->icon),
            'links' => [
                'products' => route('api.products.category', $this->id),
            ]
        ];
        $categories = Category::where('parent_id', $this->id);
        if(($categories->count() > 0)){
            $a['sub-cat']['data'] = CategoryResource::collection($categories->get());
        }
        return $a;
    }

    public function with($request)
    {
        return ['success' => true, 'status' => 200];
    }
}
