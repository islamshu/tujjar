<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\CartCollection;
use App\Models\Cart;
use App\Models\Color;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
// 
use App\BusinessSetting;
use App\Http\Controllers\Api\BaseController as BaseController;



class CartController extends BaseController
{
    public function index()
    {
        $id = auth('api')->id();
        return new CartCollection(Cart::where('user_id', $id)->latest()->get());
    }
    // public function index(){
    //             $id = auth('api')->id();

    //     $carts = Cart::where('user_id', $id)->latest()->get();
        
    //       $admin_products = array();
    //                 $seller_products = array();
              
    //                 foreach ($carts as $key => $cartItem){
    //                     if(\App\Product::find($cartItem->id)->added_by == 'admin'){
    //                         array_push($admin_products, $cartItem->id);
    //                     }
    //                     else{
    //                         $product_ids = array();
    //                         if(array_key_exists(Product::find($cartItem->id)->user_id, $seller_products)){
    //                             $product_ids = $seller_products[\App\Product::find($cartItem->id)->user_id];
    //                         }
    //                         array_push($product_ids, $cartItem->id);
    //                         $seller_products[Product::find($cartItem->id)->user_id] = $product_ids;
    //                     }
    //                 }
    //                 if (!empty($admin_products)){
    //                     $title =  get_setting('site_name') . translate('Products') ;
    //                     foreach ($admin_products as $key => $cartItem){
    //                               $product = Product::find($cartItem);
    //                               $name->product = $product->name;
    //                     }
    //                     }

    //                 }

        
        
        
        
    

    public function add(Request $request)
    {
        $product = Product::find($request->id);
        
        if(!$product){
        return $this->sendError('error','Not Found product Id');
        }

        $variant = $request->variant;
        $color = $request->color;
        $tax = 0;

        if ($variant == '' && $color == '')
            $price = $product->unit_price;
        else {
        
            $product_stock = $product->stocks->where('variant', $variant)->first();
            if(!$product_stock){
                        return $this->sendError('error','Not Found variant in  product');
            }
             $product_stock_qty = $product->stocks->where('variant', $variant)->first()->qty;
             
             if(($product_stock_qty >= 0) &&  $product->min_qty > $product_stock_qty ){
                //  dd('dd');
             return $this->sendError('error','Out of stock');
 
             }

            $price = $product_stock->price;
        }

        //discount calculation based on flash deal and regular discount
        //calculation of taxes
        $flash_deals = FlashDeal::where('status', 1)->get();
        $inFlashDeal = false;
        foreach ($flash_deals as $flash_deal) {
            if ($flash_deal != null && $flash_deal->status == 1  && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first() != null) {
                $flash_deal_product = FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first();
                if($flash_deal_product->discount_type == 'percent'){
                    $price -= ($price*$flash_deal_product->discount)/100;
                }
                elseif($flash_deal_product->discount_type == 'amount'){
                    $price -= $flash_deal_product->discount;
                }
                $inFlashDeal = true;
                break;
            }
        }
        if (!$inFlashDeal) {
            if($product->discount_type == 'percent'){
                $price -= ($price*$product->discount)/100;
            }
            elseif($product->discount_type == 'amount'){
                $price -= $product->discount;
            }
        }

        if ($product->tax_type == 'percent') {
            $tax = ($price * $product->tax) / 100;
        }
        elseif ($product->tax_type == 'amount') {
            $tax = $product->tax;
        }

        $cart=Cart::updateOrCreate([
            'user_id' => auth('api')->id(),
            'product_id' => $request->id,
            'variation' => $variant
        ], [
            'price' => $price,
            'tax' => $tax,
            'shipping_cost' => BusinessSetting::where('type','shipping_cost')->first()->value,
            'quantity' => DB::raw('quantity + 1')
        ]);

        return $this->sendResponse($cart,'add to cart sussefly');

    }

    public function changeQuantity(Request $request)
    {
        $cart = Cart::find($request->id);
        if ($cart != null) {
            if ($cart->product->stocks->where('variant', $cart->variation)->first()->qty >= $request->quantity) {
                $cart->update([
                    'quantity' => $request->quantity
                ]);

                return response()->json(['message' => 'Cart updated'], 200);
            }
            else {
                return response()->json(['message' => 'Maximum available quantity reached'], 200);
            }
        }

        return response()->json(['message' => 'Something went wrong'], 200);
    }

    public function destroy($id)
    {
        Cart::destroy($id);
        return response()->json(['message' => 'Product is successfully removed from your cart'], 200);
    }
}
