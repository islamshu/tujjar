<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Cart;
use App\Models\Product;
use App\Models\OrderDetail;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\BusinessSetting;
use App\Http\Controllers\ClubPointController;
use App\User;
use DB;
use Auth;
use App\Address;
use App\Http\Controllers\ThawaniController;
use App\Http\Controllers\Api\BaseController as BaseController;
use Mail;
use App\Mail\InvoiceEmailManager;

class OrderController extends BaseController
{
    public function make_order(Request $request){
    
      if(auth('api')->check()){
            if ($request->address_id == null) {
                flash(translate("Please add shipping address"))->warning();
                return back();
            }
            $address = Address::findOrFail($request->address_id);
            $data['name'] = auth('api')->user()->name;
            $data['email'] =auth('api')->user()->email;
            $data['address'] = $address->address;
            $data['country'] = $address->country;
            $data['city'] = $address->governorate_id;
            $data['postal_code'] = $address->state_id;
            $data['phone'] = $address->phone;
            $data['checkout_type'] = $request->checkout_type;
        } else {
            if($request->payment_type == 'wallet'){
                return $this->sendError('error ','you need to login to use wallet');

            }
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['address'] = $request->address;
            $data['country'] = $request->country;
            $data['city'] = $request->governorate_id;
            $data['postal_code'] = $request->state_id;
            $data['phone'] = $request->phone;
            $data['checkout_type'] = $request->checkout_type;
        }

        $shipping_info = $data;
        // $request->session()->put('shipping_info', $shipping_info);

        $subtotal = 0;
        $tax = 0;
          $shipping = BusinessSetting::where('type','shipping_cost')->first()->value;
        foreach (json_decode($request->cart) as $key => $cartItem) {
            // dd($cartItem);
            $subtotal += $cartItem['price'] * $cartItem['quantity'];
          $tax += $cartItem['tax'] * $cartItem['quantity'];

        }

        $total = $subtotal + $tax + $shipping;

        if (Session::has('coupon_discount')) {
            $total -= Session::get('coupon_discount');
        }

        return view('frontend.delivery_info');
        // return view('frontend.payment_select', compact('total'));
    }

    public function store_delivery_info(Request $request)
    {
        $request->session()->put('owner_id', $request->owner_id);
        // dd(Session()->all());

        if (Session::has('cart') && count(Session::get('cart')) > 0) {
            $cart = $request->session()->get('cart', collect([]));
            $cart = $cart->map(function ($object, $key) use ($request) {
                if (\App\Product::find($object['id'])->user_id == $request->owner_id) {
                    if ($request['shipping_type_' . $request->owner_id] == 'pickup_point') {
                        $object['shipping_type'] = 'pickup_point';
                        $object['pickup_point'] = $request['pickup_point_id_' . $request->owner_id];
                    } else {
                        $object['shipping_type'] = 'home_delivery';
                    }
                }
                return $object;
            });

            $request->session()->put('cart', $cart);

            $cart = $cart->map(function ($object, $key) use ($request) {
                if (\App\Product::find($object['id'])->user_id == $request->owner_id) {
                    $object['shipping'] = getShippingCost($key);
                } else {
                    $object['shipping'] = 0;
                }
                return $object;
            });

            $request->session()->put('cart', $cart);

            $subtotal = 0;
            $tax = 0;
              $shipping = BusinessSetting::where('type','shipping_cost')->first()->value;
            foreach (Session::get('cart') as $key => $cartItem) {
                $subtotal += $cartItem['price'] * $cartItem['quantity'];
                 $tax += $cartItem['tax'] * $cartItem['quantity'];    
            }

            $total = $subtotal + $tax + $shipping;

            if (Session::has('coupon_discount')) {
                $total -= Session::get('coupon_discount');
            }

            return view('frontend.payment_select', compact('total'));
        } else {
            flash(translate('Your Cart was empty'))->warning();
            return redirect()->route('home');
        }
    }
    public function processOrder(Request $request)
    {
        
         if(auth('api')->check()){
            if ($request->address_id == null) {
                flash(translate("Please add shipping address"))->warning();
                return back();
            }
            $address = Address::findOrFail($request->address_id);
            $data['name'] = auth('api')->user()->name;
            $data['email'] =auth('api')->user()->email;
            $data['address'] = $address->address;
            $data['country'] = $address->country;
            $data['city'] = $address->governorate_id;
            $data['postal_code'] = $address->state_id;
            $data['phone'] = $address->phone;
            $data['checkout_type'] = $request->checkout_type;
            $user_id=auth('api')->user()->id;
            $gest_id=null;
       
        } else {
             if($request->payment_type == 'wallet'){
                return $this->sendError('error ','you need to login to use wallet');

            }
            
            $gest_id = mt_rand(100000, 999999);
            $user_id=null;
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['address'] = $request->address;
            $data['country'] = $request->country;
            $data['city'] = $request->governorate_id;
            $data['postal_code'] = $request->state_id;
            $data['phone'] = $request->phone;
            $data['checkout_type'] = $request->checkout_type;
        }


        
        
        
        
        $shippingAddress= $data;

        // $user_id=Auth::id();
        $cartItems = json_decode($request->cart);


        $shipping = 0;
        $admin_products = array();
        $seller_products = array();
        //
       $subtotal = 0;
        $tax = 0;
          $shipping = BusinessSetting::where('type','shipping_cost')->first()->value;
        foreach ($cartItems as $key => $cartItem) {
            // dd($cartItem->price);
            $subtotal += $cartItem->price * $cartItem->quantity  ;
          $tax += $cartItem->tax *  $cartItem->quantity;

        }

        $total = $subtotal + $tax + $shipping;

        // if ($request ->coupon_discount != null) {
        //     $total -= Session::get('coupon_discount');
        // }
        // dd($request->coupon_discount);
        $order = Order::create([
            'user_id' =>$user_id,
            'guest_id'=>$gest_id,
            'shipping_address' => json_encode($shippingAddress),
            'payment_type' => $request->payment_type,
            'payment_status' => 'unpaid',
            'grand_total' => $total,    //// 'grand_total' => $request->grand_total + $shipping,
            'coupon_discount' =>$request->coupon_discount,
            'code' => date('Ymd-his'),
            'date' => strtotime('now')
        ]);

        foreach ($cartItems as $cartItem) {
            $product = Product::findOrFail($cartItem->id);
            if ($cartItem->variant) {
                $cartItemVariation = $cartItem->variant;
                $product_stocks = $product->stocks->where('variant', $cartItem->variant)->first();
                $product_stocks->qty -= $cartItem->quantity;
                $product_stocks->save();
            } else {
                $product->update([
                    'current_stock' => DB::raw('current_stock - ' . $cartItem->quantity)
                ]);
            }

       

            // save order details
            OrderDetail::create([
                'order_id' => $order->id,
                'seller_id' => $product->user_id,
                'product_id' => $product->id,
                'variation' => $cartItem->variant,
                'price' => $cartItem->price * $cartItem->quantity,
                'tax' => $cartItem->tax * $cartItem->quantity,
                'shipping_cost' => BusinessSetting::where('type','shipping_cost')->first()->value,
                'quantity' => $cartItem->quantity,
                'shipping_type'=>'home_delivery',
                'payment_status' =>'unpaid'
            ]);
            $product->update([
                'num_of_sale' => DB::raw('num_of_sale + ' . $cartItem->quantity)
            ]);
        
        // apply coupon usage
        // if ($request->coupon_code != '') {
        //     CouponUsage::create([
        //         'user_id' => Auth::id(),
        //         'coupon_id' => Coupon::where('code', $request->coupon_code)->first()->id
        //     ]);
        // }
        // calculate commission
        }
        $commission_percentage = BusinessSetting::where('type', 'vendor_commission')->first()->value;
        foreach ($order->orderDetails as $orderDetail) {
            if ($orderDetail->product->user->user_type == 'seller') {
                $seller = $orderDetail->product->user->seller;
                $price = $orderDetail->price + $orderDetail->tax + $orderDetail->shipping_cost;
                $seller->admin_to_pay = ($request->payment_type == 'cash_on_delivery') ? $seller->admin_to_pay - ($price * $commission_percentage) / 100 : $seller->admin_to_pay + ($price * (100 - $commission_percentage)) / 100;
                $seller->save();
            }
        }
        if($request->payment_type == 'thawani'){
             $thawani = new ThawaniController;
                    return $thawani->api_shipp($request,$order);
        }
                    
if(auth('api')->check()){
      
        if ($request->payment_type == 'wallet') {
         
                     $user = auth('api')->user();
                
                    if ($user->balance >= $order->grand_total) {
                        $user->balance -= $order->grand_total;
                    
                        $user->save();
                                       

                        return $this->checkout_done($order->id, 'wallet');
                    }else{
                                return $this->sendError('erorr' , 'There is not enough balance');

                    }
                    
        }
}
        
   
    }
        public function checkout_done($order_id, $payment)
    {
                    //   dd('ss');


        $order = Order::findOrFail($order_id);
        // dd($order);
        $order->payment_status = 'paid';
        $order->payment_details = $payment;
        $order->save();

        if (\App\Addon::where('unique_identifier', 'affiliate_system')->first() != null && \App\Addon::where('unique_identifier', 'affiliate_system')->first()->activated) {
            $affiliateController = new AffiliateController;
            $affiliateController->processAffiliatePoints($order);
        }

        // if (\App\Addon::where('unique_identifier', 'club_point')->first() != null && \App\Addon::where('unique_identifier', 'club_point')->first()->activated) {
        //     $clubpointController = new ClubPointController;
        //     $clubpointController->processClubPoints($order);
            
        // }
        if (\App\Addon::where('unique_identifier', 'seller_subscription')->first() == null || !\App\Addon::where('unique_identifier', 'seller_subscription')->first()->activated) {
            if (BusinessSetting::where('type', 'category_wise_commission')->first()->value != 1) {
                $commission_percentage = BusinessSetting::where('type', 'vendor_commission')->first()->value;
                foreach ($order->orderDetails as $key => $orderDetail) {
                    $orderDetail->payment_status = 'paid';
                    $orderDetail->save();
                    if ($orderDetail->product->user->user_type == 'seller') {
                        $seller = $orderDetail->product->user->seller;
                        $seller->admin_to_pay = $seller->admin_to_pay + ($orderDetail->price * (100 - $commission_percentage)) / 100 + $orderDetail->tax + $orderDetail->shipping_cost;
                        $seller->save();
                    }
                }
            } else {
                foreach ($order->orderDetails as $key => $orderDetail) {
                    $orderDetail->payment_status = 'paid';
                    $orderDetail->save();
                    if ($orderDetail->product->user->user_type == 'seller') {
                        $commission_percentage = $orderDetail->product->category->commision_rate;
                        $seller = $orderDetail->product->user->seller;
                        $seller->admin_to_pay = $seller->admin_to_pay + ($orderDetail->price * (100 - $commission_percentage)) / 100 + $orderDetail->tax + $orderDetail->shipping_cost;
                        $seller->save();
                    }
                }
            }
        } else {
            foreach ($order->orderDetails as $key => $orderDetail) {
                dd($orderDetail);
                $orderDetail->payment_status = 'paid';
                $orderDetail->save();
                if ($orderDetail->product->user->user_type == 'seller') {
                    $seller = $orderDetail->product->user->seller;
                    $seller->admin_to_pay = $seller->admin_to_pay + $orderDetail->price + $orderDetail->tax + $orderDetail->shipping_cost;
                    $seller->save();
                }
            }
        }
      

        $order->commission_calculated = 1;
        $order->save();
        if(env('MAIL_USERNAME') != null){
            try {
                Mail::to($request->session()->get('shipping_info')['email'])->queue(new InvoiceEmailManager($array));
                Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
            } catch (\Exception $e) {

            }
        }
        return $this->sendResponse('order_complerte' , 'Payment completed');


    }

    public function store(Request $request)
    {
        return $this->processOrder($request);
    }
}
