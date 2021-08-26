<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\Color;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Product;
use App\Notifications\OrderSeller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Order;
use App\Models\OrderDetail;
use App\Models\Coupon;
use App\Models\CouponUsage;
use Mail;
use App\User;
use App\Mail\InvoiceEmailManager;
use App\Address;
use App\BusinessSetting;
use App\Http\Controllers\ThawaniController;
use App\Http\Controllers\Api\BaseController as BaseController;

class CartController extends BaseController
{
    public function get_count_cart()
    {
        $admin_products = array();
        $seller_products = array();
        if (auth('api')->check()) {
            $user = auth('api')->user();
            $carts = $user->carts;
        } else {
            $coo = request()->cookie('cookes');
            $carts = Cart::where('cokkeies', $coo)->get();
        }
        if ($carts == null) {
            return $this->sendError('لا يوجد لديك سلة', 'لا يوجد لديك سلة');
        }
        foreach ($carts as $cart) {
            if ($cart->product) {
                if ($cart->product->added_by == 'admin') {
                    array_push($admin_products, $cart['product_id']);
                } else {
                    $product_ids = array();
                    if (array_key_exists(Product::find($cart['product_id'])->user_id, $seller_products)) {
                        $product_ids = $seller_products[Product::find($cart['product_id'])->user_id];
                    }
                    array_push($product_ids, $cart['product_id']);
                    $seller_products[$cart->product->user_id] = $product_ids;
                }
            }
        }
        if (!empty($admin_products)) {
            $cart_count = count($admin_products);
        }else{
            $cart_count = count($seller_products);
        }
        return $this->sendResponse($cart_count, 'this is all carts item');
    }

    public function index()
    {
        $admin_products = array();
        $seller_products = array();
        $link = url('/') . '/api/v1';
        if (auth('api')->check()) {
            $user = auth('api')->user();
            $carts = $user->carts;
        } else {
            $coo = request()->cookie('cookes');
            $carts = Cart::where('cokkeies', $coo)->get();
        }
        if ($carts == null) {
            return $this->sendError('لا يوجد لديك سلة', 'لا يوجد لديك سلة');
        }
        foreach ($carts as $cart) {
            if ($cart->product) {
                if ($cart->product->added_by == 'admin') {
                    array_push($admin_products, $cart['product_id']);
                } else {
                    $product_ids = array();
                    if (array_key_exists(Product::find($cart['product_id'])->user_id, $seller_products)) {
                        $product_ids = $seller_products[Product::find($cart['product_id'])->user_id];
                    }
                    array_push($product_ids, $cart['product_id']);
                    $seller_products[$cart->product->user_id] = $product_ids;
                }
            }
        }
        $item = [];
        $array_broduct = array();
        if (!empty($admin_products)) {
            $subtotal = 0;
            $tax = 0;
            $shipping = BusinessSetting::where('type', 'shipping_cost')->first()->value;
            foreach ($admin_products as $key => $cartItem) {
                $product = Product::find($cartItem);
                $flash_deal_discount = 0;
                $flash_deal_products = FlashDealProduct::where('product_id',$product->id)->get();
                if ($flash_deal_products) {
                    foreach ($flash_deal_products as $deal){
                        $flash_deal = $deal->flashDeal;
                        if ($flash_deal->featured == 1) {
                            $flash_deal_discount_type = $deal->discount_type;
                            if ($flash_deal_discount_type == 'amount') {
                                $flash_deal_discount = $deal->discount;
                            }elseif ($flash_deal_discount_type == 'percent'){
                                $flash_deal_discount = (getPrice($product) * $deal->discount)/100;
                            }
                        }
                    }
                }
                $pro = getPrice($product) - $flash_deal_discount;
                if (auth('api')->check()) {
                    $id = auth('api')->id();
                    $cart = Cart::where('user_id', $id)->where('product_id', $product->id)->first();
                    $subtotal += ($pro * $cart->quantity);
                    $tax += ($cart->tax * $cart->quantity);
                } else {
                    $id = request()->cookie('cookes');
                    $cart = Cart::where('cokkeies', $id)->where('product_id', $product->id)->first();
                    $subtotal += ($pro * $cart->quantity);
                    $tax += ($cart->tax * $cart->quantity);
                }
                $array_broduct['product_admin']['products'][$key]['product']['id'] = $product->id;
                $array_broduct['product_admin']['products'][$key]['product']['name_ar'] = $product->name_ar;
                $array_broduct['product_admin']['products'][$key]['product']['name_en'] = $product->name;
                $array_broduct['product_admin']['products'][$key]['product']['image'] = api_asset($product->thumbnail_img);
                $array_broduct['product_admin']['products'][$key]['cart_id'] = $cart->id;
                $array_broduct['product_admin']['products'][$key]['delete'] = route('carts.destroy', $cart->id);
                $pizza = $cart->variation;
                $array_broduct['product_admin']['products'][$key]['variation'] = $pizza;
                if ($pizza != null) {
                    $pieces = explode("-", $pizza);
                    foreach ($pieces as $piece) {
                        $color = Color::where('name', $piece)->first();
                        $size = DB::table('size_att')->where('name_ar', $piece)->Orwhere('name_en', $piece)->first();
                        $fabric = DB::table('fabrics')->where('name_ar', $piece)->Orwhere('name_en', $piece)->first();
                        if ($color) {
                            $array_broduct['product_admin']['products'][$key]['color_code'] = $color->code;
                            $array_broduct['product_admin']['products'][$key]['color_name'] = $color->name;
                        } elseif ($size) {
                            $array_broduct['product_admin']['products'][$key]['size'] = $size->name_en;
                        } elseif ($fabric) {
                            $array_broduct['product_admin']['products'][$key]['fabric_ar'] = $fabric->name_ar;
                            $array_broduct['product_admin']['products'][$key]['fabric_en'] = $fabric->name_en;
                        }
                    }
                }
                $array_broduct['product_admin']['products'][$key]['price'] = single_price_api((double)$cart->price);
                $array_broduct['product_admin']['products'][$key]['quantity'] = $cart->quantity;
                $array_broduct['product_admin']['products'][$key]['created_at'] = $cart->created_at;
                $array_broduct['product_admin']['link_to_pay'] = $link . '/retrun_to_paid/' . $product->user_id . '/' . $id;
                $array_broduct['product_admin']['user_id'] = $id;
                $array_broduct['product_admin']['seller_id'] = $product->user_id;
            }
            $array_broduct['product_admin']['shop_name_en'] = $product->user->shop ? $product->user->shop->name:'';
            $array_broduct['product_admin']['shop_name_ar'] = $product->user->shop ? $product->user->shop->name_ar:'';
            $array_broduct['product_admin']['subtotal'] = single_price_api($subtotal);
            $array_broduct['product_admin']['tax'] = single_price_api($tax);
            $array_broduct['product_admin']['total shipping'] = single_price_api($shipping);
            $array_broduct['product_admin']['total'] = single_price_api($subtotal + $tax + $shipping);
            array_push($item, $array_broduct['product_admin']);
            dd(count($array_broduct['product_admin']));
        }
        foreach ($seller_products as $key => $cartItemqq) {
            $subtotal = 0;
            $tax = 0;
            $shipping = BusinessSetting::where('type', 'shipping_cost')->first()->value;
            $array_broduct['seller']['products'] = array();
            foreach ($cartItemqq as $k => $v) {
                $product = Product::find($v);
                $flash_deal_discount = 0;
                $flash_deal_products = FlashDealProduct::where('product_id',$product->id)->get();
                if ($flash_deal_products) {
                    foreach ($flash_deal_products as $deal){
                        $flash_deal = $deal->flashDeal;
                        if ($flash_deal->featured == 1) {
                            $flash_deal_discount_type = $deal->discount_type;
                            if ($flash_deal_discount_type == 'amount') {
                                $flash_deal_discount = $deal->discount;
                            }elseif ($flash_deal_discount_type == 'percent'){
                                $flash_deal_discount = (getPrice($product) * $deal->discount)/100;
                            }
                        }
                    }
                }
                $pro = getPrice($product) - $flash_deal_discount;
                if (auth('api')->check()) {
                    $id = auth('api')->id();
                    $cart = Cart::where('user_id', $id)->where('product_id', $product->id)->first();
                    $subtotal += ($pro * $cart->quantity);
                    $tax += ($cart->tax * $cart->quantity);
                } else {
                    $id = request()->cookie('cookes');
                    $cart = Cart::where('cokkeies', $id)->where('product_id', $product->id)->first();
                    $subtotal += ($pro * $cart->quantity);
                    $tax += ($cart->tax * $cart->quantity);
                }
                $array_broduct['seller']['products'][$k]['product']['id'] = $product->id;
                $array_broduct['seller']['products'][$k]['product']['name_ar'] = $product->name_ar;
                $array_broduct['seller']['products'][$k]['product']['name_en'] = $product->name;
                $array_broduct['seller']['products'][$k]['product']['image'] = api_asset($product->thumbnail_img);
                $array_broduct['seller']['products'][$k]['cart_id'] = $cart->id;
                $array_broduct['seller']['products'][$k]['delete'] = route('carts.destroy', $cart->id);
                $pizza = $cart->variation;
                $array_broduct['seller']['products'][$k]['variation'] = $pizza;
                if ($pizza != null) {
                    $pieces = explode("-", $pizza);
                    foreach ($pieces as $piece) {
                        $color = Color::where('name', $piece)->first();
                        $size = DB::table('size_att')->where('name_ar', $piece)->Orwhere('name_en', $piece)->first();
                        $fabric = DB::table('fabrics')->where('name_ar', $piece)->Orwhere('name_en', $piece)->first();
                        if ($color) {
                            $array_broduct['seller']['products'][$k]['color_code'] = $color->code;
                            $array_broduct['seller']['products'][$k]['color_name'] = $color->name;
                        } elseif ($size) {
                            $array_broduct['seller']['products'][$k]['size'] = $size->name_en;
                        } elseif ($fabric) {
                            $array_broduct['seller']['products'][$k]['fabric_ar'] = $fabric->name_ar;
                            $array_broduct['seller']['products'][$k]['fabric_en'] = $fabric->name_en;
                        }
                    }
                }
                $array_broduct['seller']['products'][$k]['price'] = single_price_api((double)$cart->price);
                $array_broduct['seller']['products'][$k]['quantity'] = $cart->quantity;
                $array_broduct['seller']['products'][$k]['created_at'] = $cart->created_at;
                $array_broduct['seller']['link_to_pay'] = $link . '/retrun_to_paid/' . $product->user_id . '/' . $id;
                $array_broduct['seller']['user_id'] = $id;
                $array_broduct['seller']['seller_id'] = $product->user_id;
            }
            $array_broduct['seller']['shop_name_en'] = $product->user->shop ? $product->user->shop->name:'';
            $array_broduct['seller']['shop_name_ar'] = $product->user->shop ? $product->user->shop->name_ar:'';
            $array_broduct['seller']['subtotal'] = single_price_api($subtotal);
            $array_broduct['seller']['tax'] = single_price_api($tax);
            $array_broduct['seller']['total shipping'] = single_price_api($shipping);
            $array_broduct['seller']['total'] = single_price_api($subtotal + $tax + $shipping);
            array_push($item, $array_broduct['seller']);
        }
        return $this->sendResponse($item, 'this is all carts item');
    }

    public function add(Request $request)
    {
        $product = Product::find($request->id);
        if (!$product)
            return $this->sendError('error', 'Not Found product Id');
        $variant = null;
        $color = $request->color;
        if ($color != null)
            $cc = Color::where('code', $color)->first()->name;
        $fabric = $request->fabric;
        if ($fabric != null) {
            $fab = DB::table('fabrics')->where('name_ar', $fabric)->Orwhere('name_en', $fabric)->first()->name_ar;
        }
        $size = $request->size;
        if ($color != null && $size != null && $fabric != null)
            $variant = $cc . '-' . $size . '-' . $fab;
        elseif ($color != null && $size == null && $fabric != null)
            $variant = $cc . '-' . $fab;
        elseif ($color == null && $size != null && $fabric != null)
            $variant = $size . '-' . $fab;
        elseif ($color == null && $size == null && $fabric != null)
            $variant = $fab;
        elseif ($color != null && $size != null && $fabric == null)
            $variant = $cc . '-' . $size;
        elseif ($color != null && $size == null && $fabric == null)
            $variant = $cc;
        $tax = 0;
        $qty = $product->current_stock;
        if ($request->qty == null)
            $request->qty = 1;
        if ($qty < $request->qty)
            return $this->sendError(translate('Out of stock'), 'Out of stock');
        if ($variant == '' && $color == '')
            $price = getPrice($product);
        else {
            $product_stock = $product->stocks->where('variant', $variant)->first();
            if (!$product_stock)
                return $this->sendError(translate('Not Found variant in  product'));
            $product_stock_qty = $product_stock->qty;
            if (($product_stock_qty >= 0) && $product->min_qty > $product_stock_qty)
                return $this->sendError('Out of stock', 'Out of stock');
            $price = $product_stock->price;
        }
        $flash_deals = FlashDeal::where('status', 1)->get();
        $inFlashDeal = false;
        foreach ($flash_deals as $flash_deal) {
            if ($flash_deal != null && $flash_deal->status == 1 && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first() != null) {
                $flash_deal_product = FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first();
                if ($flash_deal_product->discount_type == 'percent')
                    $price -= ($price * $flash_deal_product->discount) / 100;
                elseif ($flash_deal_product->discount_type == 'amount')
                    $price -= $flash_deal_product->discount;
                $inFlashDeal = true;
                break;
            }
        }
        if (!$inFlashDeal)
            $price = getPrice($product);
        if (get_setting('tax_type') == 'percent')
            $tax = ($price * get_setting('tax')) / 100;
        if (get_setting('tax_type') == 'amount')
            $tax = get_setting('tax');
        $cookes = null;
        $user_id = null;
        if (auth('api')->check())
            $user_id = auth('api')->id();
        else {
            if (request()->cookie('cookes') == null) {
                $bytes = random_bytes(20);
                $cookes = bin2hex($bytes);
                setcookie('cookes', $cookes, time() + 60 * 60 * 24 * 365);
            } else
                $cookes = request()->cookie('cookes');
        }
        $cart = Cart::updateOrCreate([
            'user_id' => $user_id,
            'cokkeies' => $cookes,
            'owner_id' => $product->user_id,
            'product_id' => $request->id,
            'variation' => $variant
        ], [
            'price' => $price,
            'tax' => $tax,
            'shipping_cost' => BusinessSetting::where('type', 'shipping_cost')->first()->value,
            'quantity' => DB::raw('quantity + ' . $request->qty)
        ]);
        $cart['price'] = single_price_api($cart->price);
        $cart['tax'] = single_price_api($cart->tax);
        return $this->sendResponse($cart, 'add to cart sussefly');
    }

    public function changeQuantity(Request $request)
    {
        $cart = Cart::find($request->cart_id);
        if ($cart) {
            if ($cart->variation == null) {
                $product = Product::find($cart->product_id);
                if ($request->quantity <= $product->current_stock) {
                    $cart->update(['quantity' => $request->quantity]);
                    return $this->sendResponse('success', 'Cart updated');
                }
            }
            if ($cart->product->stocks->where('variant', $cart->variation)->first()->qty >= $request->quantity) {
                $cart->update(['quantity' => $request->quantity]);
                return $this->sendResponse('success', 'Cart updated');
            } else {
                return $this->sendError('Maximum available quantity reachedd');
            }
        }
        return $this->sendError('Something went wrong');
    }

    public function destroy($id)
    {
        $cart = Cart::find($id);
        if (!$cart)
            return $this->sendError('error occer');
        $cart = Cart::destroy($id);
        return $this->sendResponse($cart, 'Product is successfully removed from your cart');
    }

    public function make_order_id(Request $request, $id, $id2)
    {
        $user = User::find($id2);
        if ($user) {
            if ($request->address_id == null)
                return $this->sendError(translate("Please add shipping address"));
            $address = Address::where('user_id', auth('api')->id())->where('set_default', 1)->first();
            if (!$address)
                return $this->sendError(translate('you dont have defult address'));
            $data['name'] = $user->name;
            $data['email'] = $user->email;
            $data['address'] = $address->address;
            $data['country'] = $address->country;
            $data['city'] = $address->governorate_id;
            $data['postal_code'] = $address->state_id;
            $data['phone'] = $address->phone;
            $data['checkout_type'] = $request->checkout_type;
            $user_id = $user->id;
            $gest_id = null;
        } else {
            if ($request->payment_type == 'wallet')
                return $this->sendError('error ', translate('you need to login to use wallet'));
            $gest_id = mt_rand(100000, 999999);
            $user_id = null;
            $data['name'] = $request->name;
            $data['email'] = $request->email;
            $data['address'] = $request->address;
            $data['country'] = $request->country;
            $data['city'] = $request->governorate_id;
            $data['postal_code'] = $request->state_id;
            $data['phone'] = $request->phone;
            $data['checkout_type'] = $request->checkout_type;
        }
        $shippingAddress = $data;
        if (is_numeric($id2)) {
            $cart = Cart::where('owner_id', $id)->where('user_id', $id2)->get();
        }else{
            $cart = Cart::where('owner_id', $id)->where('cokkeies', $id2)->get();
        }
        if ($cart == null) {
            return $this->sendError('لا يوجد سلة', 'لا يوجد سلة');
        }
        $cartItems = json_decode($cart);
        $subtotal = 0;
        $tax = 0;
        $shipping = BusinessSetting::where('type', 'shipping_cost')->first()->value;
        foreach ($cartItems as $key => $cartItem) {
            $subtotal += ($cartItem->price * $cartItem->quantity);
            $tax += ($cartItem->tax * $cartItem->quantity);
        }
        $total = ($subtotal + $tax + $shipping);
        $coupon_discount = 0.00;
        if ($user) {
            if ($request->code != null) {
                $coupon = Coupon::where('code', $request->code)->first();
                if ($coupon != null) {
                    if (strtotime(date('d-m-Y')) >= $coupon->start_date && strtotime(date('d-m-Y')) <= $coupon->end_date) {
                        if (CouponUsage::where('user_id', $id2)->where('coupon_id', $coupon->id)->first() == null) {
                            $coupon_details = json_decode($coupon->details);
                            if ($coupon->type == 'cart_base') {
                                $subtotal = 0;
                                $tax = 0;
                                foreach ($cartItems as $key => $cartItem) {
                                    $subtotal += $cartItem->price * $cartItem->quantity;
                                    $tax += $cartItem->tax * $cartItem->quantity;
//                                    $arr[] = 0;
                                }
//                                $max = max($arr);
//                                $shipping = $max;
                                $sum = $subtotal + $tax + $shipping;
                                if ($sum >= $coupon_details->min_buy) {
                                    if ($coupon->discount_type == 'percent') {
                                        $coupon_discount = ($sum * $coupon->discount) / 100;
                                        if ($coupon_discount > $coupon_details->max_discount)
                                            $coupon_discount = $coupon_details->max_discount;
                                    } elseif ($coupon->discount_type == 'amount')
                                        $coupon_discount = $coupon->discount;
                                }
                            } elseif ($coupon->type == 'product_base') {
                                $coupon_discount = 0;
                                foreach ($cartItems as $key => $cartItem) {
                                    foreach ($coupon_details as $key => $coupon_detail) {
                                        if ($coupon_detail->product_id == $cartItem->id) {
                                            if ($coupon->discount_type == 'percent')
                                                $coupon_discount += $cartItem->price * $coupon->discount / 100;
                                            elseif ($coupon->discount_type == 'amount')
                                                $coupon_discount += $coupon->discount;
                                        }
                                    }
                                }
                            }
                        } else
                            return $this->sendError(translate('You already used this coupon!'));
                    } else
                        return $this->sendError(translate('Coupon expired!'));
                } else
                    return $this->sendError(translate('Invalid coupon!'));
            }
        } else
            $coupon_discount = 0.00;
        $total -= $coupon_discount;
        $order = Order::create([
            'user_id' => $user_id,
            'guest_id' => $gest_id,
            'shipping_address' => json_encode($shippingAddress),
            'payment_type' => $request->payment_type,
            'payment_status' => 'unpaid',
            'grand_total' => $total,
            'coupon_discount' => $coupon_discount,
            'code' => date('Ymd-his'),
            'date' => strtotime('now')
        ]);
        foreach ($cartItems as $cartItem) {
            $product = Product::findOrFail($cartItem->product_id);
            if ($cartItem->variation) {
                $product_stocks = $product->stocks->where('variant', $cartItem->variation)->first();
                $product_stocks->qty -= $cartItem->quantity;
                $product_stocks->save();
            } else
                $product->update(['current_stock' => DB::raw('current_stock - ' . $cartItem->quantity)]);
            OrderDetail::create([
                'order_id' => $order->id,
                'seller_id' => $product->user_id,
                'product_id' => $product->id,
                'variation' => $cartItem->variation,
                'price' => $cartItem->price * $cartItem->quantity,
                'tax' => $cartItem->tax * $cartItem->quantity,
                'shipping_cost' => BusinessSetting::where('type', 'shipping_cost')->first()->value,
                'quantity' => $cartItem->quantity,
                'shipping_type' => 'home_delivery',
                'payment_status' => 'unpaid'
            ]);
            $product->update(['num_of_sale' => DB::raw('num_of_sale + ' . $cartItem->quantity)]);
            $commission_percentage = BusinessSetting::where('type', 'vendor_commission')->first()->value;
            foreach ($order->orderDetails as $orderDetail) {
                if ($orderDetail->product->user->user_type == 'seller') {
                    $seller = $orderDetail->product->user->seller;
                    $price = $orderDetail->price + $orderDetail->tax + $orderDetail->shipping_cost;
                    $seller->admin_to_pay = ($request->payment_type == 'cash_on_delivery') ? $seller->admin_to_pay - ($price * $commission_percentage) / 100 : $seller->admin_to_pay + ($price * (100 - $commission_percentage)) / 100;
                    $seller->save();
                }
            }
            if ($request->payment_type == 'thawani') {
                $thawani = new ThawaniController;
                return $thawani->api_shipp($request, $order, $id, $id2);
            }else{
                if (auth('api')->check()) {
                    $user = auth('api')->user();
                    if ($user->balance >= $order->grand_total) {
                        $user->balance -= $order->grand_total;
                        $user->save();
                        return $this->checkout_done($order->id, 'wallet', $id, $id2);
                    }
                }
                return $this->sendError('erorr', translate('There is not enough balance'));
            }
        }
    }

    public function checkout_done($order_id, $payment, $id, $id2)
    {
        $order = Order::findOrFail($order_id);
        $order->payment_status = 'paid';
        $order->payment_details = $payment;
        $order->save();
        $cart = Cart::where('owner_id', $id)->where('user_id', $id2)->count();
        if ($cart == 0)
            $cart = Cart::where('owner_id', $id)->where('cokkeies', $id2)->get();
        else
            $cart = Cart::where('owner_id', $id)->where('user_id', $id2)->get();
        foreach ($cart as $k) {
            $cc = Cart::find($k->id);
            $cc->destroy($k->id);
            $cc->save();
        }
        $affiliate_system = \App\Addon::where('unique_identifier', 'affiliate_system')->first();
        if ($affiliate_system != null && $affiliate_system->activated) {
            $affiliateController = new AffiliateController;
            $affiliateController->processAffiliatePoints($order);
        }
        $seller_subscription = \App\Addon::where('unique_identifier', 'seller_subscription')->first();
        if ($seller_subscription == null || !$seller_subscription->activated) {
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
        if (env('MAIL_USERNAME') != null) {
            try {
                Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
            } catch (\Exception $e) {
            }
        }
        return $this->sendResponse('order_complerte', translate('Payment completed'));
    }
}
