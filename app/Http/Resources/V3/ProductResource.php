<?php

namespace App\Http\Resources\V3;

use App\Models\V3\FlashDeal;
use App\Models\V3\ProductStock;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\V3\Color;
use Share;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        $flash_deal_discount = 0;
        $flash_deal = FlashDeal::where('featured',1)->first();
        if ($flash_deal) {
            $flash_deal_product = $flash_deal->flashDealProducts()->where('product_id',$this->id)->first();
            if ($flash_deal_product) {
                $flash_deal_discount_type = $flash_deal->discount_type;
                if ($flash_deal_discount_type == 'amount') {
                    $flash_deal_discount = $flash_deal->discount;
                }elseif ($flash_deal_discount_type == 'percent'){
                    $flash_deal_discount = (getPrice($this) * $flash_deal->discount)/100;
                }
            }
        }
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
        $qty = 0;
        $stock = ProductStock::where('product_id',$this->id)->first();
        if ($stock) {
            $qty = $stock->qty;
        }
//        return [uploaded_asset_nullable($this->thumbnail_img)];
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name,
            'category'=>$category,
            'brand'=>$brand,
            'colors' => $this->colors != null ? $this->color(json_decode($this->colors)) : [],
            'size'=> $this->choice_options != null ? $this->convertToSize(json_decode($this->choice_options)) : [],
            'fabric'=> $this->choice_options != null ? $this->convertTofabric(json_decode($this->choice_options)) : [],
            'photos' => uploaded_assets_nullable($this->photos),
            'thumbnail_image' => uploaded_asset_nullable($this->thumbnail_img),
            // 'base_price' => single_price_api((double)$this->unit_price),
            // 'base_discounted_price' => single_price_api((double)getPrice($this)-$flash_deal_discount),
            'base_price' => home_base_price_api_v3($this),
            'base_discounted_price' => home_discounted_base_price_api_v3($this),
            'todays_deal' => (integer)$this->todays_deal,
            'featured' => (integer)$this->featured,
            'unit' => $this->unit,
            'description_ar' => strip_tags($this->description_ar),
            'description_en' => strip_tags($this->description),
            'discount' => (double)$this->discount,
            'discount_type' => $this->discount_type,
            'flash_deal_discount' => (double)$flash_deal_discount,
            'club_point' => $this->earn_point,
            'rating' => (double)$this->rating,
            'sales' => (integer)$this->num_of_sale,
            'published' => $this->published,
            'social_share' => $links,
            'slug' => $link,
            'product_stock' => $qty,
            'min_product' => $this->min_qty,
            'product_price' => $this->unit_price,
            'product_cost' => $this->purchase_price,
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
