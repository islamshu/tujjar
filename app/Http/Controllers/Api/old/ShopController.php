<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ShopCollection;
use App\Http\Resources\SellerCollection;
use App\Seller;
use Illuminate\Http\Request;
use Hash;
use App\BusinessSetting;
use App\Product;
use App\Models\Shop;
use Auth;
use App\User;
use Validator;
use App\Http\Controllers\ThawaniController;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Vendorpackege;
use App\Notifications\EmailVerificationNotification;
class ShopController extends BaseController
{
    public function seller(){
         $vendors =new SellerCollection(Seller::where('verification_status', 1)->get());
                 return $this->sendResponse($vendors,'shops');

    }
    public function bestseller(){
         $array = array();
        foreach (Seller::where('verification_status', 1)->get() as $key => $seller) {
            if($seller->user != null && $seller->user->shop != null){
                $total_sale = 0;
                foreach ($seller->user->products as $key => $product) {
                    $total_sale += $product->num_of_sale;
                }
                $array[$seller->id] = $total_sale;
            }
             $sellers = array();
      
        
        }
         asort($array);
         foreach($array as $key=>$value){
            $sellers[]= $key;
        }
        $seell=array();
        foreach($sellers as $seller){
            if($sell = Seller::find($seller)){
                 $total = 0;
                $rating = 0;
               foreach ($sell->user->products as $key => $seller_product) {
                $total += $seller_product->reviews->count();
                $rating += $seller_product->reviews->sum('rating');
                
               }
            
                $item['id']=$sell->id;
                 $item['name_ar']=$sell->user->shop->name_ar;
                $item['name']=$sell->user->shop->name;
                $item['logo']=api_asset($sell->user->shop->logo);
                if($total >0 ){
                  $item['rating'] = $rating/$total;   
                }else{
                   $item['rating'] = 0;  
                }
                $item['link'] = route('shops.info', $sell->user->shop->id);
               
            array_push($seell, $item);
            }
        }
        return $seell ;
        
    }
    
    public function index()
    {
        
        $shop= new ShopCollection(Shop::all());
        return $this->sendResponse($shop,'shops');

    }

    public function info($id)
    {
        $shop= new ShopCollection(Shop::where('id', $id)->get());
        return $this->sendResponse($shop,'shop');
    }

    public function shopOfUser($id)
    {
        $id = Auth::id();
         $shop= new ShopCollection(Shop::where('user_id', $id)->get());
                return $this->sendResponse($shop,'shop Of User');

    }

    public function allProducts($id)
    {
        $shop = Shop::findOrFail($id);
         $shops= new ProductCollection(Product::where('user_id', $shop->user_id)->latest()->paginate(10));
                        return $this->sendResponse($shops,'shop allProducts');

    }
     public function allProductsLogin()
    {
        
      
        // dd(Auth::user()->shop->id);
        $shop = Shop::findOrFail(Auth::user()->shop->id);
        
         $shops= new ProductCollection(Product::where('user_id', $shop->user_id)->latest()->paginate(10));
                        return $this->sendResponse($shops,'shop allProducts');

    }

    public function topSellingProducts($id)
    {
        $shop = Shop::findOrFail($id);
       $shops= new ProductCollection(Product::where('user_id', $shop->user_id)->orderBy('num_of_sale', 'desc')->limit(4)->get());
          return $this->sendResponse($shops,'shop topSellingProducts');
    }

    public function featuredProducts($id)
    {
        $shop = Shop::findOrFail($id);
        $shops= new ProductCollection(Product::where(['user_id' => $shop->user_id, 'featured'  => 1])->latest()->get());
                  return $this->sendResponse($shops,'shop featuredProducts');

    }

    public function newProducts($id)
    {
        $shop = Shop::findOrFail($id);
       $shops= new ProductCollection(Product::where('user_id', $shop->user_id)->orderBy('created_at', 'desc')->limit(10)->get());
                          return $this->sendResponse($shops,'shop newProducts');

    }
    public function create(Request $request){
          if(!auth('api')->check()){
              
               $validator =    Validator::make($request->all(), [

            'email' => 'required|string|email|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
            'name'=>'required'
        ]);
        if ($validator -> fails()) {
            # code...
            return $this->sendError('error validation', $validator->errors());
        }
         
        
                $user = new User;
                $user->name = $request->name;
                $user->email = $request->email;
                $user->phone = $request->phone;
                $user->user_type = "seller";
                $user->password = Hash::make($request->password);
                $user->vendor_pakege_id = $request->vendor_pakege;
                $user->save();
                
        }else{
   $user = auth('api')->user();
//   dd($user);
if ($user->user_type == 'seller' || $user->user_type == 'admin'){
                return $this->sendError('Not allowed');

}
            if($user->customer != null){
                $user->customer->delete();
            }
            $user->user_type = "seller";
            $user->save();
            $user->assignRole('seller');
        }
        if(Seller::where('user_id', $user->id)->first() == null){

        $seller = new Seller;
        $seller->user_id = $user->id;
        $seller->vendor_pakege_id = $request->vendor_pakege;
        $seller->save();
        }
         if(Shop::where('user_id', $user->id)->first() == null){
            $shop = new Shop;
            $shop->user_id = $user->id;
            $shop->name = $request->shop_name_en;
            $shop->name_ar = $request->shop_name_ar;
            $shop->address = $request->address;
  

            $shop->vendor_pakege_id = $request->vendor_pakege;


            $shop->slug = preg_replace('/\s+/', '-', $request->name).'-'.$shop->id;

            if($shop->save()){
                
                auth()->login($user, false);
                if(BusinessSetting::where('type', 'email_verification')->first()->value != 1){
                    $user->email_verified_at = date('Y-m-d H:m:s');
                    $user->save();
                }
                else {
                    $user->notify(new EmailVerificationNotification());
                }
     
                    $thawani = new ThawaniController;
                    return $thawani->vendor_create($request);
                 return $this->sendResponse($shop,'shop created sussefuly');
     
            }
            else{
                $seller->delete();
                $user->user_type == 'customer';
                $user->save();
            }
        }
        
        
        
        
        
        
    }
    public function piad_for_vendor(Request $request){
        // dd('d');
        $paid = Seller::where('user_id',Auth::id())->first()->paid;
  if($paid != 1){
            $thawani = new ThawaniController;
                    return $thawani->vendor_create($request);
                 return $this->sendResponse($shop,'shop created sussefuly');
    }else{
                       return $this->sendError('you have already paid');
  
    }
    }

    public function product_published($id,Request $request)
    {
        $product=Product::where('id',$id)->first();

     
                $product->published = $request->published;
        
                $product->save();
                $shop= 'sussefuly';
                
             return $this->sendResponse($shop,'product edit sussefuly ');

   
        
    }
    public function product_featured($id,Request $request)
    {
        $type= Auth::user()->user_type;
        $product=Product::find($id);
        if($product){
        if($type == 'seller'){
            // if($product->user_id == Auth::id()){
                $product->featured = $request->featured;
                $product->save();
                $shop= 'sussefuly';
             return $this->sendResponse($shop,'product edit sussefuly ');

            // }
        }
        }
    }
    public function delete_product($id){
               $product=Product::find($id);
               $product->destroy($id);
               $product->save();
            $shop= 'sussefuly';

              return $this->sendResponse($shop,'product deleted sussefuly ');

    }
    
    
    
    
}
