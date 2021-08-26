<?php

namespace App\Http\Resources;

use App\City2;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Review;
use App\Models\Attribute;
use App\Product;
use App\Shop;
use App\Seller;
use Share;

class ProductDetailCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => $this->collection->map(function ($data) {
                $detailedProduct = Product::find($data->id);
                $commentable = 0;
                if (auth('api')->check()) {
                    foreach ($detailedProduct->orderDetails as $key => $orderDetail) {
                        if ($orderDetail->delivery_status == 'delivered' && $orderDetail->order != null && $orderDetail->order->user_id == auth('api')->user()->id && \App\Review::where('user_id', auth('api')->user()->id)->where('product_id', $detailedProduct->id)->first() == null) {
                            $commentable = 1;
                        }
                    }
                }
                return [
                    'id' => (integer)$data->id,
                    'name_en' => $data->name,
                    'name_ar' => $data->name_ar,
                    'added_by' => $this->add_by($data),
                    'can_review' => $commentable,
                    'social_share' => $this->share($data),
                    'user' => $this->getShop($data),
                    'category' => [
                        'name' => $this->catt($data),
                        'banner' => $data->category ? api_asset($data->category->banner) : '',
                        'icon' => $data->category ? $data->category->icon : '',
                        'links' => [
                            'products' => route('api.products.category', $data->category_id),
                            'sub_categories' => route('subCategories.index', $data->category_id)
                        ]
                    ],
                    'shop_fetures' => $this->add_by_verfiy($data),
                    'brand' => [
                        'id' => $data->brand != null ? $data->brand->id : null,
                        'name' => $data->brand != null ? $data->brand->name : null,
                        'logo' => $data->brand != null ? api_asset($data->brand->logo) : null,
                        'links' => [
                            'products' => $data->brand != null ? route('api.products.brand', $data->brand_id) : null
                        ]
                    ],
                    'club_point' => $data->earn_point,
                    'photos' => $this->convertPhotos(explode(',', $data->photos)),
                    'thumbnail_image' => api_asset($data->thumbnail_img),
                    'tags' => explode(',', $data->tags),
                    'price_lower' => single_price_api((double)getPrice($data)),
                    'price_higher' => single_price_api((double)$data->unit_price),
                    'choice_options' => $this->convertToChoiceOptions(json_decode($data->choice_options)),
                    'colors' => json_decode($data->colors),
                    'todays_deal' => (integer)$data->todays_deal,
                    'featured' => (integer)$data->featured,
                    'current_stock' => (integer)$data->current_stock,
                    'unit' => $data->unit,
                    'discount' => (double)$data->discount,
                    'discount_type' => $data->discount_type,
                    'tax' => (double)$data->tax,
                    'size' => $this->convertToSize(json_decode($data->choice_options)),
                    'fabric' => $this->convertTofabric(json_decode($data->choice_options)),
                    'tax_type' => $data->tax_type,
                    'shipping_type' => $data->shipping_type,
                    'shipping_cost' => (double)$data->shipping_cost,
                    'number_of_sales' => (integer)$data->num_of_sale,
                    'rating' => (double)$data->rating,
                    'rating_count' => (integer)Review::where(['product_id' => $data->id])->count(),
                    'description_en' => $data->description,
                    'description_ar' => $data->description_ar,
                    'isFav' => $this->isFav($data),
                    'links' => [
                        'reviews' => route('api.reviews.index', $data->id),
                        'related' => route('products.related', $data->id)
                    ]
                ];
            })
        ];
    }

    public function getShop($data)
    {
        $total = 0;
        $rating = 0;
        foreach ($data->user->products as $key => $seller_product) {
            $total += $seller_product->reviews->count();
            $rating += $seller_product->reviews->sum('rating');
        }
        if ($total > 0){
            $rate = $rating/$total;
        }else{
            $rate = 0;
        }
        if ($data->user) {
            $user_data = [
                'name' => $data->user->name,
                'email' => $data->user->email,
                'avatar' => $data->user->avatar,
                'avatar_original' => api_asset($data->user->avatar_original),
                'shop_name' => $data->added_by == 'admin' ? '' : $data->user->shop->name,
                'shop_logo' => $data->added_by == 'admin' ? '' : uploaded_asset($data->user->shop->logo),
                'shop_link' => $data->added_by == 'admin' ? '' : route('shops.info', $data->user->shop->id)
            ];
            $shop = $data->user->shop;
            if ($shop){
                $user_data = array_merge($user_data,[
                    'name_ar' => $shop->name_ar,
                    'name_en' => $shop->name,
                    'user' => [
                        'name' => $shop->user->name,
                        'email' => $shop->user->email,
                        'avatar' => $shop->user->avatar,
                        'avatar_original' => $shop->user->avatar_original
                    ],
                    'share_links'=>$this->shareShop($data),
                    'shop_fetures'=>$data->verify,
                    'logo' => uploaded_asset_nullable($shop->logo),
                    'sliders' => $this->convertPhotos(explode(',', $shop->sliders)),
                    'address_id'=>$shop->address,
                    'address_ar' => City2::find($shop->address)->name,
                    'address_en' => City2::find($shop->address)->name_en,
                    'rate'=>$rate,
                    'facebook' => $shop->facebook,
                    'twitter' => $shop->twitter,
                    'youtube' => $shop->youtube,
                    'instagram' => $shop->instagram,
                    'links' => [
                        'featured' => route('shops.featuredProducts', $shop->id),
                        'top' => route('shops.topSellingProducts',  $shop->id),
                        'new' => route('shops.newProducts', $shop->id),
                        'all' => route('shops.allProducts', $shop->id),
                        'brands' => route('shops.brands', $shop->id)
                    ]
                ]);
            }
        }
        return $user_data;
    }

    public function isFav($data)
    {
        $status = false;
        if (auth('api')->check()) {
            if ($data->wishlists()->where('user_id', auth('api')->id())->count() > 0) {
                $status = true;
            }
        }
        return $status;
    }

    public function catt($data)
    {
        if ($data->category != null) {
            return $data->category->name;
        } else {
            return null;
        }
    }

    public function add_by($data)
    {
        $add = $data->added_by;
        if ($add == 'admin') {
            $name = 'Tujjar Oman';
        } else {
            //   dd(Shop::where('user_id',$data->user_id)->first());
            $name = Shop::where('user_id', $data->user_id)->first()->name;
        }
        return $name;
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
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

    public function shareShop($data){
        // dd();
        $link = 'https://www.tujjar-oman.com/shop/'.$data->user->shop->slug;
        $links= Share::load($link, 'seller in tujjar oman store')->services('facebook', 'whatsapp', 'twitter');
        $links['link']=$link;
        return $links;

    }

    protected function convertToChoiceOptions($data)
    {
        $result = array();
        foreach ($data as $key => $choice) {
            $item['name'] = $choice->attribute_id;
            $item['title'] = Attribute::find($choice->attribute_id)->name;
            $item['options'] = $choice->values;
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

    public function add_by_verfiy($data)
    {
        $add = $data->added_by;
        if ($add == 'admin') {
            $name = 1;
        } else {
            //   dd(Shop::where('user_id',$data->user_id)->first());
            $name = Seller::where('user_id', $data->user_id)->first()->verify;
        }
        return $name;
    }


    protected function convertPhotos($data)
    {
        $result = array();
        foreach ($data as $key => $item) {
            array_push($result, api_asset($item));
        }
        return $result;
    }
}
