<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Color;
use Share;

class ProductCollection extends ResourceCollection
{
    protected $flash_deal;

    public function __construct($resource,$flash_deal=null)
    {
        parent::__construct($resource);
        $this->flash_deal = $flash_deal;
    }

    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                $flash_deal_discount = 0;
                if ($this->flash_deal) {
                    $flash_deal_product = $this->flash_deal->flashDealProducts()->where('product_id',$data->id)->first();
                    if ($flash_deal_product) {
                        $flash_deal_discount_type = $flash_deal_product->discount_type;
                        if ($flash_deal_discount_type == 'amount') {
                            $flash_deal_discount = $flash_deal_product->discount;
                        }elseif ($flash_deal_discount_type == 'percent'){
                            $flash_deal_discount = (getPrice($data) * $flash_deal_product->discount)/100;
                        }
                    }
                }
                return [
                    'id' => $data->id,
                    'name_ar' => $data->name_ar,
                    'name_en' => $data->name,
                    'category' => $this->category($data),
                    'brand' => $this->brand($data),
                    'colors' => $this->color(json_decode($data->colors)),
                    'size' => $this->convertToSize(json_decode($data->choice_options)),
                    'fabric' => $this->convertTofabric(json_decode($data->choice_options)),
//                    'photos' => explode(',', api_asset($data->photos)),
//                    'thumbnail_image' => api_asset($data->thumbnail_img),
                    'photos' => uploaded_assets_nullable($data->photos),
                    'thumbnail_image' => uploaded_asset_nullable($data->thumbnail_img),
                    'base_price' => home_base_price_api($data->id),
                    'base_discounted_price' => home_discounted_base_price_api($data->id),
                    'todays_deal' => (integer)$data->todays_deal,
                    'featured' => (integer)$data->featured,
                    'unit' => $data->unit,
                    'description_ar' => strip_tags($data->description_ar),
                    'description_en' => strip_tags($data->description),
                    'discount' => (double)$data->discount,
                    'discount_type' => $data->discount_type,
                    'flash_deal_discount' => (double)$flash_deal_discount,
                    'club_point' => $data->earn_point,
                    'rating' => (double)$data->rating,
                    'sales' => (integer)$data->num_of_sale,
                    'published' => $data->published,
                    'social_share' => $this->share($data),
                    'slug' => $data->slug,
                    'product_stock' => @$data->stocks()->first()->qty,
                    'min_product' => $data->min_qty,
                    'product_price' => $data->unit_price,
                    'product_cost' => $data->purchase_price,
                    'isFav' => $this->isFav($data),
                    'links' => [
                        'details' => route('products.show', $data->id),
                        'reviews' => route('api.reviews.index', $data->id),
                        'related' => route('products.related', $data->id),
                        'top_from_seller' => route('products.topFromSeller', $data->id)
                    ]
                ];
            })
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

    public function share($data)
    {
        $link = 'https://tujjar-oman.com/product/' . $data->slug;
        if ($data->description_en != null) {
            $dec = $data->description_en;

        } else {
            $dec = 'a';

        }

        $links = Share::load($link, $dec)->services('facebook', 'twitter', 'whatsapp');
        $links['link'] = $link;
        return $links;
    }

    protected function brand($data)
    {
        $result = array();
        // foreach ($data as $key => $color) {
        if ($data->brand != null) {
            $item['id'] = $data->brand->id;
            $item['name_ar'] = $data->brand->name;
            $item['name_en'] = $data->brand->name_en;
        } else {
            $item['id'] = null;
            $item['name_ar'] = null;
            $item['name_en'] = null;
        }

        // $item['options'] = $choice->values;
        array_push($result, $item);
        // }
        return $result;
    }

    protected function category($data)
    {
        $result = array();
        // foreach ($data as $key => $color) {
        if ($data->category != null) {

            $item['id'] = $data->category->id;
            $item['name_ar'] = $data->category->name;
            $item['name_en'] = $data->category->name_en;
        } else {
            $item['id'] = null;
            $item['name_ar'] = null;
            $item['name_en'] = null;
        }

        // $item['options'] = $choice->values;
        array_push($result, $item);
        // }
        return $result;
    }

    protected function color($data)
    {
        $result = array();
        foreach ($data as $key => $color) {
            $item['code'] = $color;
            $item['name'] = @Color::where('code', $color)->first()->name;
            array_push($result, $item);
        }
        return $result;
    }

    protected function convertTosize($data)
    {
        $result = array();
        foreach ($data as $key => $choice) {
            if ($choice->attribute_id == 1) {

                $result = $choice->values;

            }


        }
        // array_push($result, $item);

        return $result;
    }

    protected function convertTofabric($data)
    {
        $result = array();
        foreach ($data as $key => $choice) {
            if ($choice->attribute_id == 2) {

                $result = $choice->values;

            }


        }
        // array_push($result, $item);

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
