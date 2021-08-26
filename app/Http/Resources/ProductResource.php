<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Color;
use Share;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $link = 'https://tujjar-oman.com/product/'.$this->slug;
        $dec = $this->description_en !=null ? $this->description_en:'a';
        $links =  Share::load($link, $dec)->services('facebook','twitter','whatsapp');
        $brand = array();
        $data['id'] = @$this->brand->id;
        $data['name_ar'] = @$this->brand->name;
        $data['name_en'] = @$this->brand->name_en;
        array_push($brand, $data);
        $category = array();
        $data['id'] =@$this->category->id;
        $data['name_ar'] = @$this->category->name;
        $data['name_en'] = @$this->category->name_en;
        array_push($category, $data);

        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name,
            'category'=>$category,
            'brand'=>$brand,
            'colors' => $this->color(json_decode($this->colors)),
            'size'=>$this->convertToSize(json_decode($this->choice_options)),
            'fabric'=>$this->convertTofabric(json_decode($this->choice_options)),
            'photos' => explode(',', api_asset($this->photos)),
            'thumbnail_image' => api_asset($this->thumbnail_img),
            'base_price' => single_price_api((double) $this->unit_price),
            'base_discounted_price' => single_price_api((double) getPrice($this)),
            'todays_deal' => (integer) $this->todays_deal,
            'featured' =>(integer) $this->featured,
            'unit' => $this->unit,
            'description_ar' => strip_tags($this->description_ar),
            'description_en' => strip_tags($this->description),
            'discount' => (double) $this->discount,
            'discount_type' => $this->discount_type,
            'club_point'=>$this->earn_point,
            'rating' => (double) $this->rating,
            'sales' => (integer) $this->num_of_sale,
            'published'=>$this->published,
            'social_share'=>$links,
            'slug'=>$this->slug,
            'isFav' => $this->isFav($this),
            'links' => [
                'details' => route('products.show', $this->id),
                'reviews' => route('api.reviews.index', $this->id),
                'related' => route('products.related', $this->id),
                'top_from_seller' => route('products.topFromSeller', $this->id)
            ]
        ];
    }

    public function isFav($data)
    {
        $status = false;
        if (auth('api')->check()) {
            if ($data->wishlists()->where('user_id',auth('api')->id())->count() > 0) {
                $status = true;
            }
        }
        return $status;
    }

    protected function color($item){
        $result = array();
        foreach ($item as $key => $color) {
            $data['id'] =$color;
            $data['name'] = Color::where('code',$color)->first()->name;
            array_push($result, $data);
        }
        return $result;
    }

    protected function convertTosize($item){
        $result = array();
        foreach ($item as $key => $choice) {
            if($choice->attribute_id == 1){
                $result = $choice->values;
            }
        }
        return $result;
    }

    protected function convertTofabric($item){
        $result = array();
        foreach ($item as $key => $choice) {
            if($choice->attribute_id == 2){
                $result = $choice->values;
            }
        }
        return $result;
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
