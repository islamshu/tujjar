<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\BannerCollection;
use App\Http\Resources\BrandCollection;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\FlashDealCollection;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\SellerCollection;
use App\Http\Resources\V3\FlashDealResource;
use App\Http\Resources\V3\SliderResource;
use App\Http\Resources\V3\ProductResource;
use App\Http\Resources\V3\ProductWithoutFlashDeal;
use App\Models\Brand;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\GeneralSetting;
use App\Product;
use App\Seller;
use App\Upload;
use DB;
use Illuminate\Http\Request;

class HomeController extends BaseController
{
    // mkjn.dev
    public function getHome(Request $request)
    {
        $sliders = get_setting('home_slider_images');
        if ($sliders) {
            $sliders = json_decode($sliders);
            if ($sliders){
                $sliders = Upload::whereIn('id',$sliders )->get();
                $sliders = SliderResource::collection($sliders);
            }
        }
        $data['sliders'] = $sliders;

        $flashes = FlashDeal::where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
        if ($flashes->count() > 0)
            $flashes = FlashDealResource::collection($flashes);
        $data['flash_deals'] = $flashes;

        $today_deals = Product::where('todays_deal', 1)->latest()->get();
        if ($today_deals) {
            $today_deals = ProductWithoutFlashDeal::collection($today_deals);
        }
        $data['today_deals'] = $today_deals;

        $data['best_products'] = ProductWithoutFlashDeal::collection(Product::orderBy('num_of_sale', 'desc')->limit(20)->get());
        $data['fetured_products'] = ProductWithoutFlashDeal::collection(Product::where('featured', 1)->latest()->get());
        $data['banners'] = new BannerCollection(json_decode(get_setting('home_banner1_images'), true));

        foreach (Seller::where('verification_status', 1)->get() as $key => $seller) {
            if ($seller->user != null && $seller->user->shop != null) {
                $total_sale = 0;
                foreach ($seller->user->products as $key => $product) {
                    $total_sale += $product->num_of_sale;
                }
                $sellers[] = $seller->id;
            }
        }
        $seell = array();
        $sellers = Seller::whereIn('id', $sellers)->get();
        foreach ($sellers as $seller) {
            $total = $seller->user->seller_reviews->count();
            $rating = $seller->user->seller_reviews->sum('rating');
            $item['id'] = $seller->id;
            $item['name_ar'] = $seller->user->shop->name_ar;
            $item['name'] = $seller->user->shop->name;
            $item['logo'] = api_asset($seller->user->shop->logo);
            if ($total > 0) {
                $item['rating'] = $rating / $total;
            } else {
                $item['rating'] = 0;
            }
            $item['link'] = route('shops.info', $seller->user->shop->id);
            array_push($seell, $item);
        }
        $data['sellers'] = $seell;

        $homepageCategories = BusinessSetting::where('type', 'home_categories')->first();
        $homepageCategories = json_decode($homepageCategories->value);
        $cat= new CategoryCollection(Category::whereIn('id', $homepageCategories)->get());
        $data['categories'] = $cat;

        $cat=new CategoryCollection(Category::where('level', 0)->get());
        $data['featured'] = $cat;


        $data['colors'] = DB::table('colors')->get();;

        $data['fabrics'] =DB::table('fabrics')->get();

        $data['sizes'] =DB::table('size_att')->get();

        $data['brands'] = new BrandCollection(Brand::all());

        $langs = Request()->header('lang');
        if ($langs == 'en') {
            $pages = DB::table('page_translations')->whereIn('page_id', [3,5])->where('lang', 'en')->get();
        } else {
            $pages = DB::table('page_translations')->whereIn('page_id', [3,5])->where('lang', 'sa')->get();
        }

        $data['terms'] = strip_tags($pages->where('page_id', 5)->first()->content);
        $data['reutrns'] = strip_tags($pages->where('page_id', 3)->first()->content);

        $data['whatsapp'] = GeneralSetting::first()->phone;


        $data['vendors'] = new SellerCollection(Seller::where('verification_status', 1)->get());

        return $this->sendResponse($data,'get all home');
    }
}
