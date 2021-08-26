<?php

namespace App\Http\Resources\V3;

use App\Models\V3\City2;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\V3\Review;
use App\Models\V3\Attribute;
use App\Models\V3\Product;
use App\Models\V3\Shop;
use App\Models\V3\Seller;
use Share;

class ProductDetailResource extends JsonResource
{
    public function toArray($request)
    {
        $detailedProduct = Product::find($this->id);
        $commentable = 0;
        if (auth('api')->check()) {
            foreach ($detailedProduct->orderDetails as $key => $orderDetail) {
                if ($orderDetail->order != null && $orderDetail->order->user_id == auth('api')->user()->id && Review::where('user_id', auth('api')->user()->id)->where('product_id', $detailedProduct->id)->first() == null) {
                    $commentable = 1;
                }
            }
        }
        return [
            'id' => (integer)$this->id,
            'name_en' => $this->name,
            'name_ar' => $this->name_ar,
            'added_by' => $this->add_by($this),
            'can_review' => $commentable,
            'social_share' => $this->share($this),
            'user' => $this->getShop($this),
            'category' => [
                'name' => $this->catt($this),
                'banner' => $this->category ? api_asset($this->category->banner) : '',
                'icon' => $this->category ? $this->category->icon : '',
                'links' => [
                    'products' => route('api.products.category', $this->category_id),
                    'sub_categories' => route('subCategories.index', $this->category_id)
                ]
            ],
            'shop_fetures' => $this->add_by_verfiy($this),
            'brand' => [
                'id' => $this->brand != null ? $this->brand->id : null,
                'name' => $this->brand != null ? $this->brand->name : null,
                'logo' => $this->brand != null ? api_asset($this->brand->logo) : null,
                'links' => [
                    'products' => $this->brand != null ? route('api.products.brand', $this->brand_id) : null
                ]
            ],
            'club_point' => $this->earn_point,
            'photos' => $this->convertPhotos(explode(',', $this->photos)),
            'thumbnail_image' => api_asset($this->thumbnail_img),
            'tags' => explode(',', $this->tags),
            'price_lower' => single_price_api((double)getPrice($this)),
            'price_higher' => single_price_api((double)$this->unit_price),
            'choice_options' => $this->convertToChoiceOptions(json_decode($this->choice_options)),
            'colors' => json_decode($this->colors),
            'todays_deal' => (integer)$this->todays_deal,
            'featured' => (integer)$this->featured,
            'current_stock' => (integer)$this->current_stock,
            'unit' => $this->unit,
            'discount' => (double)$this->discount,
            'discount_type' => $this->discount_type,
            'tax' => (double)$this->tax,
            'size' => $this->convertToSize(json_decode($this->choice_options)),
            'fabric' => $this->convertTofabric(json_decode($this->choice_options)),
            'tax_type' => $this->tax_type,
            'shipping_type' => $this->shipping_type,
            'shipping_cost' => (double)$this->shipping_cost,
            'number_of_sales' => (integer)$this->num_of_sale,
            'rating' => (double)$this->rating,
            'rating_count' => (integer)Review::where(['product_id' => $this->id])->count(),
            'description_en' => $this->description,
            'description_ar' => $this->description_ar,
            'isFav' => $this->isFav($this),
            'links' => [
                'reviews' => route('api.reviews.index', $this->id),
                'related' => route('products.related', $this->id)
            ]
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
