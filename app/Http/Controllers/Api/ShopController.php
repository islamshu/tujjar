<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ShopCollection;
use App\Http\Resources\SellerCollection;
use App\Seller;
use App\Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Hash;
use App\BusinessSetting;
use App\Product;
use App\Models\Shop;
use App\Brand;
use App\User;
use App\Http\Controllers\ThawaniController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Notifications\EmailVerificationNotification;

class ShopController extends BaseController
{
    public function seller()
    {
        $vendors = new SellerCollection(Seller::where('verification_status', 1)->get());
        return $this->sendResponse($vendors, translate('shops'));
    }

    public function bestseller()
    {
        $array = array();
        foreach (Seller::where('verification_status', 1)->get() as $key => $seller) {
            if ($seller->user != null && $seller->user->shop != null) {
                $total_sale = 0;
                foreach ($seller->user->products as $key => $product) {
                    $total_sale += $product->num_of_sale;
                }
                $array[$seller->id] = $total_sale;
            }
            $sellers = array();
        }
        asort($array);
        foreach ($array as $key => $value) {
            $sellers[] = $key;
        }
        $seell = array();
        foreach ($sellers as $seller) {
            if ($sell = Seller::find($seller)) {
                $total = 0;
                $rating = 0;
                foreach ($sell->user->products as $key => $seller_product) {
                    $total += $seller_product->reviews->count();
                    $rating += $seller_product->reviews->sum('rating');
                }
                $item['id'] = $sell->id;
                $item['name_ar'] = $sell->user->shop->name_ar;
                $item['name'] = $sell->user->shop->name;
                $item['logo'] = api_asset($sell->user->shop->logo);
                if ($total > 0) {
                    $item['rating'] = $rating / $total;
                } else {
                    $item['rating'] = 0;
                }
                $item['link'] = route('shops.info', $sell->user->shop->id);
                array_push($seell, $item);
            }
        }
        return $seell;
    }

    public function index()
    {
        $shop = new ShopCollection(Shop::all());
        return $this->sendResponse($shop, translate('shops'));
    }

    public function info($id)
    {
        $shop = new ShopCollection(Shop::where('id', $id)->get());
        return $this->sendResponse($shop, translate('shop'));
    }

    public function shopOfUser()
    {
        $shop = new ShopCollection(Shop::where('user_id', auth('api')->id())->get());
        return $this->sendResponse($shop, translate('shop Of User'));
    }

    public function allProducts($id)
    {
        $shop = Shop::findOrFail($id);
        $shops = new ProductCollection(Product::where('user_id', $shop->user_id)->latest()->get());
        return $this->sendResponse($shops, translate('shop allProducts'));
    }

    public function allProductsLogin()
    {
        $shop = Shop::findOrFail(auth('api')->user()->shop->id);
        $shops = new ProductCollection(Product::where('user_id', $shop->user_id)->latest()->get());
        return $this->sendResponse($shops, translate('shop allProducts'));
    }

    public function topSellingProducts($id)
    {
        $shop = Shop::findOrFail($id);
        $shops = new ProductCollection(Product::where('user_id', $shop->user_id)->orderBy('num_of_sale', 'desc')->limit(4)->get());
        return $this->sendResponse($shops, translate('shop topSellingProducts'));
    }

    public function featuredProducts($id)
    {
        $shop = Shop::findOrFail($id);
        $shops = new ProductCollection(Product::where(['user_id' => $shop->user_id, 'featured' => 1])->latest()->get());
        return $this->sendResponse($shops, translate('shop featuredProducts'));
    }

    public function shop_update(Request $request)
    {
        $shop = auth('api')->user()->shop;
        $shop->name_ar = $request->name_ar;
        $shop->name = $request->name_en;
        $shop->address = $request->address;
        $shop->facebook = $request->facebook;
        $shop->twitter = $request->twitter;
        $shop->youtube = $request->youtube;
        $shop->instagram = $request->instagram;
        $shop->save();
        $shops = new ShopCollection(Shop::where('id', $shop->id)->get());
        return $this->sendResponse($shops, translate('shop updated'));
    }

    public function newProducts($id)
    {
        $shop = Shop::findOrFail($id);
        $shops = new ProductCollection(Product::where('user_id', $shop->user_id)->orderBy('created_at', 'desc')->limit(10)->get());
        return $this->sendResponse($shops, translate('shop newProducts'));
    }

    public function create(Request $request)
    {
        if (!auth('api')->check()) {
            if (User::where('email', $request->email)->first())
                return $this->sendError(translate('Email is already in use'));
            if (User::where('phone', $request->phone)->first())
                return $this->sendError(translate('Phone is already in use'));
            if ($request->password != $request->confirm_password)
                return $this->sendError(translate('Password does not match'));
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->user_type = "seller";
            $user->password = Hash::make($request->password);
            $user->vendor_pakege_id = $request->vendor_pakege;
            $user->save();
        } else {
            $user = auth('api')->user();
            if ($user->user_type == 'seller' || $user->user_type == 'admin')
                return $this->sendError(translate('Not allowed'));
            if ($user->customer != null)
                $user->customer->delete();
            $user->user_type = "seller";
            $user->save();
            $user->assignRole('seller');
        }
        if (Seller::where('user_id', $user->id)->first() == null) {
            $seller = new Seller;
            $seller->user_id = $user->id;
            $seller->vendor_pakege_id = $request->vendor_pakege;
            $seller->save();
        }
        if (Shop::where('user_id', $user->id)->first() == null) {
            $shop = new Shop;
            $shop->user_id = $user->id;
            $shop->name = $request->shop_name_en;
            $shop->name_ar = $request->shop_name_ar;
            $shop->address = $request->address;
            $shop->slug = preg_replace('/\s+/', '-', $request->shop_name_en) . '-' . $shop->id;
            $shop->vendor_pakege_id = $request->vendor_pakege;
            if ($shop->save()) {
                auth()->login($user, false);
                if (BusinessSetting::where('type', 'email_verification')->first()->value != 1) {
                    $user->email_verified_at = date('Y-m-d H:m:s');
                    $user->save();
                } else
                    $user->notify(new EmailVerificationNotification());
                $tokenResult = $user->createToken('Personal Access Token');
                $thawani = new ThawaniController;
                $url = $thawani->vendor_create($request, $user);
                return $this->loginSuccess($tokenResult, $user, $url);
            } else {
                $seller->delete();
                $user->user_type == 'customer';
                $user->save();
            }
        }
    }

    protected function loginSuccess($tokenResult, $user, $payment_url)
    {
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(100);
        $token->save();
        $success['access_token'] = $tokenResult->accessToken;
        if (!$session = Session::where('user_id', $user->id)->first()) {
            $session = new Session();
            $session->user_id = $user->id;
            $session->token = $tokenResult->accessToken;
            $session->save();
        } else {
            $session->token = $tokenResult->accessToken;
            $session->save();
        }
        foreach ($payment_url as $k => $v) {
            if ($k == 'original')
                $url = $v['data'];
        }
        return $this->sendResponse([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
            'user' => [
                'id' => $user->id,
                'type' => $user->user_type,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'avatar_original' => $user->avatar_original,
                'address' => $user->address,
                'country' => $user->country,
                'city' => $user->city,
                'postal_code' => $user->postal_code,
                'phone' => $user->phone,
            ],
            'payment_url' => $url
        ], translate('login succeffly'));
    }

    public function piad_for_vendor(Request $request)
    {
        $user = auth('api')->user();
        $paid = Seller::where('user_id', auth('api')->id())->first()->paid;
        if ($paid != 1) {
            $thawani = new ThawaniController;
            return $thawani->vendor_create($request, $user);
        } else {
            return $this->sendError(translate('you have already paid'));
        }
    }

    public function product_published($id, Request $request)
    {
        $product = Product::where('id', $id)->first();
        $product->published = $request->published;
        $product->save();
        return $this->sendResponse('sussefuly', translate('product edit sussefuly '));
    }

    public function product_featured($id, Request $request)
    {
        $type = auth('api')->user()->user_type;
        $product = Product::find($id);
        if ($product) {
            if ($type == 'seller') {
                $product->featured = $request->featured;
                $product->save();
                return $this->sendResponse('sussefuly', translate('product edit sussefuly '));
            }
        }
    }

    public function delete_product($id)
    {
        $product = Product::find($id);
        $product->destroy($id);
        $product->save();
        return $this->sendResponse('sussefuly', translate('product deleted sussefuly '));
    }

    public function brands($id)
    {
        $shop = Shop::findOrFail($id);
        $products = Product::where('user_id', $shop->user_id)->get();
        $brands = array();
        foreach ($products as $key => $pro) {
            if ($pro->brand_id != null)
                array_push($brands, $pro->brand_id);
        }
        $unique = collect($brands);
        $unique_data = $unique->unique()->values()->all();
        foreach ($unique_data as $key => $ke) {
            $k = Brand::find($ke);
            $brandsw[$key]['name_ar'] = $k->name;
            $brandsw[$key]['name_en'] = $k->name_en;
            $brandsw[$key]['logo'] = api_asset($k->logo);
        }
        return $this->sendResponse($brandsw, translate('all brands use in this shop'));
    }
}
