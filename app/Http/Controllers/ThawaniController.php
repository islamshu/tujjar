<?php

namespace App\Http\Controllers;

use App;
use App\Classes\thawani;
use Illuminate\Http\Request;
use Session;
use App\Order;
use Auth;
use App\Wallet;
use App\User;
use App\Session as SSesion;
use App\Seller;
use App\Vendorpackege;
use App\BusinessSetting;
use Carbon\Carbon;
use Mail;
use App\Models\Cart;
use App\Mail\InvoiceEmailManager;
use App\Notifications\OrderSeller;
use App\Traits\ApiResponser;
use App\ClubPoint;

class ThawaniController extends Controller
{
    use ApiResponser;
    public function thawani(Request $request)
    {
        $is_test = BusinessSetting::where('type', 'thawani_sandbox')->first()->value;
        if ($is_test == 1) {
            $thawani = new thawani([
                'isTestMode' => 1, ## set it to 0 to use the class in production mode
                'public_key' => 'HGvTMLDssJghr9tlN9gr4DVYt0qyBy',
                'private_key' => 'rRQ26GcsZzoEhbrP2HZvLYDbn9C9et',
            ]);
        } else {
            $thawani = new thawani([
                'isTestMode' => 0, ## set it to 0 to use the class in production mode  
                'public_key' => env('thawani_Publishable_key'),
                'private_key' => env('thawani_Secret_key'),
            ]);
        }
        if (Session::has('cart') && count(Session::get('cart')) > 0) {
            $qu = 0;
            foreach (Session::get('cart') as $key => $val) {
                $qu += $val['quantity'];
            }
            $order = Order::findOrFail(Session::get('order_id'));
            $amount = $order->grand_total * 1000;
            $order_id = Session::get('order_id');
            $customer_name = session::get('shipping_info')['name'];
            $customer_email = session::get('shipping_info')['email'];
        } elseif (Session::get('payment_type') == 'wallet_payment' && (Session::get('payment_data')['amount']) > 0) {
            $amount = Session::get('payment_data')['amount'] * 1000;
            $order_id = rand(0, 99999);
            $customer_name = Auth::user()->name;
            $customer_email = Auth::user()->email;
        } else {
            $amount = Session::get('amount_pakege') * 1000;
            $order_id = rand(0, 99999);
            $customer_name = Auth::user()->name;
            $customer_email = Auth::user()->email;
        }
        $request->op = !isset($request->op) ? '' : $request->op; ## to avoid PHP notice message
        switch ($request->op) {
            default: ## Generate payment URL
                $orderId = $order_id; ## order number based on your existing system
                $input = [
                    'client_reference_id' => rand(1000, 9999) . $orderId, ## generating random 4 digits prefix to make sure there will be no duplicate ID error
                    'products' => [
                        ['name' => 'products from ' . env('APP_NAME'), 'unit_amount' => $amount, 'quantity' => 1],
                    ],
                    'success_url' => route('thawani.done'), ## Put the link to next a page with the method checkPaymentStatus()
                    'cancel_url' => route('thawani.cancel'),
                    'metadata' => [
                        'order_id' => $order_id,
                        'customer_name' => $customer_name,
                        'customer_phone' => 656565656,
                        'customer_email' => $customer_email,
                    ]
                ];
                $url = $thawani->generatePaymentUrl($input);
                echo '<pre dir="ltr">' . print_r($thawani->responseData, true) . '</pre>';
                $request->session()->put($_SERVER['REMOTE_ADDR'], $thawani->payment_id);
                if (!empty($url)) {
                    ## method will provide you with a payment id from Thawani, you should save it to your order. You can get it using this: $thawani->payment_id
                    ## header('location: '.$url); ## Redirect to payment page
                    return redirect($url);
                }
                break;
            case 'callback': ## handle Thawani callback, you need to update order status in your database or file system, in Thawani V2.0 you need to add a link to this page in Webhooks
                $result = $thawani->handleCallback(1);
                /*
                 * $results contain some information, it will be like:
                 * $results = [
                 *  'is_success' => 0 for failed, 1 for successful
                 *  'receipt' => receipt ID, generate for transaction
                 *  'raw' => [ SESSION DATA ]
                 * ];
                 */
                if ($thawani->payment_status == 1) {
                    ## successful payment
                } else {
                    ## failed payment
                }
                break;
            case 'checkPayment':
                $session = $request->session()->get($_SERVER['REMOTE_ADDR']);
                $check = $thawani->checkPaymentStatus($session);
                if ($thawani->payment_status == 1) {
                    ## successful payment
                    echo '<h2>successful payment</h2>';
                } else {
                    ## failed payment
                    echo '<h2>payment failed</h2>';
                }
                $thawani->iprint_r($check);
                break;
            case 'createCustomer':
                $customer = $thawani->createCustomer('me@alrashdi.co');
                $thawani->iprint_r($customer);
                break;
            case 'getCustomer':
                $customer = $thawani->getCustomer('me@alrashdi.co');
                $thawani->iprint_r($customer);
                break;
            case 'deleteCustomer':
                $customer = $thawani->deleteCustomer('cus_xxxxxxxxxxxxxxx');
                $thawani->iprint_r($customer);
                break;
            case 'home':
                echo 'Get payment status from database';
                break;
        }
    }

    public function getCheckout()
    {
        $is_test = BusinessSetting::where('type', 'thawani_sandbox')->first()->value;
        if ($is_test == 1) {
            $thawani = new thawani([
                'isTestMode' => 1, ## set it to 0 to use the class in production mode
                'public_key' => 'HGvTMLDssJghr9tlN9gr4DVYt0qyBy',
                'private_key' => 'rRQ26GcsZzoEhbrP2HZvLYDbn9C9et',
            ]);
        } else {
            $thawani = new thawani([
                'isTestMode' => 0, ## set it to 0 to use the class in production mode  
                'public_key' => env('thawani_Publishable_key'),
                'private_key' => env('thawani_Secret_key'),
            ]);
        }
        $aa = SSesion::where('user_id', auth('api')->id())->first()->amount_wallet;
        $res = json_decode($aa, true);
        $amount = $res['amount'] * 1000;
        $order_id = rand(0, 99999);
        $customer_name = auth('api')->user()->name;
        $customer_phone = auth('api')->user()->phone;
        $customer_email = auth('api')->user()->email;
        $orderId = $order_id; ## order number based on your existing system
        $input = [
            'client_reference_id' => rand(1000, 9999) . $orderId, ## generating random 4 digits prefix to make sure there will be no duplicate ID error
            'products' => [
                ['name' => 'products from ' . env('APP_NAME'), 'unit_amount' => $amount, 'quantity' => 1],
            ],
            //            'customer_id' => 'cus_xxxxxxxxxxxxxxx', ## TODO: enable this when its activate from Thawani Side
            'success_url' => route('api.waliet.thawani.done', auth('api')->id()),
            'cancel_url' => route('api.waliet.thawani.cancel', auth('api')->id()),
            'metadata' => [
                'order_id' => $order_id,
                'customer_name' => $customer_name,
                'customer_phone' => $customer_phone,
                'customer_email' => $customer_email,
            ]
        ];
        $url = $thawani->generatePaymentUrl($input);
        $thawani->responseData['url'] = $url;
        Session()->put('user_id', Auth::id());
        $response = [
            'success' => true ,
            'data' => $url,
            'message' => 'open url'
        ];
        return response()->json($response , 200);
//        return view('payment_success');
    }

    public function wallitapidone($id)
    {
        $get_user = SSesion::where('user_id', $id)->first();
        $user = User::find($get_user->user_id);
        $aa = $get_user->amount_wallet;
        $res = json_decode($aa, true);
        $amount = $res['amount'];
        $user->balance = $user->balance + $amount;
        $user->save();
        $wallet = new Wallet;
        $wallet->user_id = $user->id;
        $wallet->amount = $amount;
        $wallet->payment_method = $res['payment_method'];
        $wallet->payment_details = 'thawani';
        $wallet->save();
        $get_user->amount_wallet = null;
        $get_user->save();
        return view('payment_success');
    }

    public function wallitapierror($id)
    {
        $get_user = SSesion::where('user_id', $id)->first();
        $response = [
            'success' => false,
            'message' => 'error occer'
        ];
        return response()->json($response, 404);

    }

    public function vendor_create($request, $user)
    {
// dd($user->id);
        $is_test = BusinessSetting::where('type', 'thawani_sandbox')->first()->value;
        if ($is_test == 1) {
            $thawani = new thawani([
                'isTestMode' => 1, ## set it to 0 to use the class in production mode
                'public_key' => 'HGvTMLDssJghr9tlN9gr4DVYt0qyBy',
                'private_key' => 'rRQ26GcsZzoEhbrP2HZvLYDbn9C9et',
            ]);
        } else {
            $thawani = new thawani([
                'isTestMode' => 0, ## set it to 0 to use the class in production mode  
                'public_key' => env('thawani_Publishable_key'),
                'private_key' => env('thawani_Secret_key'),
            ]);
        }
        $aa = seller::where('user_id', $user->id)->first()->vendor_pakege_id;
        $vendor = Vendorpackege::find($aa)->price;


        $amount = $vendor * 1000;
        $order_id = rand(0, 99999);
        $customer_name = $user->name;
        $customer_phone = $user->phone;
        $customer_email = $user->email;
        $orderId = $order_id; ## order number based on your existing system
        //   dd($user);
        $input = [
            'client_reference_id' => rand(1000, 9999) . $orderId, ## generating random 4 digits prefix to make sure there will be no duplicate ID error
            'products' => [

                ['name' => 'products from ' . env('APP_NAME'), 'unit_amount' => $amount, 'quantity' => 1],
            ],

            'success_url' => route('api.vendor.thawani.done', $user->id),
            'cancel_url' => route('api.vendor.thawani.cancel', $user->id),

            'metadata' => [
                'order_id' => $order_id,
                'customer_name' => $customer_name,
                'customer_phone' => $customer_phone,
                'customer_email' => $customer_email,
            ]
        ];

        $url = $thawani->generatePaymentUrl($input);
        $thawani->responseData['url'] = $url;


        $response = [
            'success' => true,
            'data' => $url,
            'message' => 'open url'
        ];
        return response()->json($response, 200);


    }

    public function vendorapidone($id)
    {
        $seller = Seller::where('user_id', $id)->first();
        $seller->paid = 1;
        $seller->save();
        $user = User::find($id);
        auth()->login($user, true);
        return view('payment_success');
    }

    public function vendorapierror($id)
    {
        $response = [
            'success' => false,
            'message' => 'error occer'
        ];
        $user = User::find($id);
        auth()->login($user, true);
        $tokenResult = $user->createToken('Personal Access Token');
        $message = translate('shop creted but not paid');

        return $this->loginSuccess($tokenResult, $user, $message);
        return response()->json($response, 404);
    }


    protected function loginSuccess($tokenResult, $user, $message)
    {
        $token = $tokenResult->token;
        $token->expires_at = Carbon::now()->addWeeks(100);
        $token->save();
        $success['access_token'] = $tokenResult->accessToken;
        if (!$session = SSesion::where('user_id', $user->id)->first()) {
            $session = new SSesion();
            $session->user_id = $user->id;
            $session->token = $tokenResult->accessToken;
            $session->save();
        } else {
            $session->token = $tokenResult->accessToken;
            $session->save();
        }
        $result = [
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString(),
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

            ]
        ];
        $message = $message;
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message
        ];
        return response()->json($response, 200);


    }


    public function api_shipp($request, $order, $id, $id2)
    {
        // dd($order);
        $is_test = BusinessSetting::where('type', 'thawani_sandbox')->first()->value;
        if ($is_test == 1) {
            $thawani = new thawani([
                'isTestMode' => 1, ## set it to 0 to use the class in production mode
                'public_key' => 'HGvTMLDssJghr9tlN9gr4DVYt0qyBy',
                'private_key' => 'rRQ26GcsZzoEhbrP2HZvLYDbn9C9et',
            ]);
        } else {
            $thawani = new thawani([
                'isTestMode' => 0, ## set it to 0 to use the class in production mode  
                'public_key' => env('thawani_Publishable_key'),
                'private_key' => env('thawani_Secret_key'),
            ]);
        }

        $amount = $order->grand_total * 1000;


        $order_id = rand(0, 99999);
        if (auth('api')->check()) {
            //   dd('dd');
            $customer_name = auth('api')->user()->name;
            $customer_phone = auth('api')->user()->phone;
            $customer_email = auth('api')->user()->email;

        } else {
            $customer_name = $request->name;
            $customer_phone = $request->phone;
            $customer_email = $request->email;
        }

        $orderId = $order_id; ## order number based on your existing system
        $input = [
            'client_reference_id' => rand(1000, 9999) . $orderId, ## generating random 4 digits prefix to make sure there will be no duplicate ID error
            
            'products' => [

                ['name' => 'products from ' . env('APP_NAME'), 'unit_amount' => (int)$amount, 'quantity' => 1],
            ],


            'success_url' => route('api.shiping.thawani.done', [$order->id, $id, $id2]),
            'cancel_url' => route('api.shiping.thawani.cancel', $order->id),

            'metadata' => [
                'order_id' => $order_id,
                'customer_name' => $customer_name,
                'customer_phone' => $customer_phone,
                'customer_email' => $customer_email,
            ]
        ];
        //   dd($input);

        $url = $thawani->generatePaymentUrl($input);

        $thawani->responseData['url'] = $url;


        //
        $seee = Session()->put('user_id', Auth::id());


        $response = [
            'success' => true,
            'data' => $url,
            'message' => 'open url'
        ];
        return response()->json($response, 200);
    }

    public function apishipingdone($order_id, $id, $id2,Request $request)
    {
        $order = Order::find($order_id);
        // dd($order_id);
        $order->payment_status = 'paid';
        //   $payment = 'thwani';
        $order->payment_details = 'thwani';
        $order->save();
        $cart = Cart::where('owner_id', $id)->where('user_id', $id2)->count();
        $test=0;
        if ($cart == 0) {
            $cart = Cart::where('owner_id', $id)->where('cokkeies', $id2)->get();
            
        } else {
            $cart = Cart::where('owner_id', $id)->where('user_id', $id2)->get();
            $test = 1;
        }
        foreach ($cart as $k) {
            $cc = Cart::find($k->id);
            $cc->destroy($k->id);
            $cc->save();
        }
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
                $orderDetail->payment_status = 'paid';
                $orderDetail->save();
                if ($orderDetail->product->user->user_type == 'seller') {
                    $seller = $orderDetail->product->user->seller;
                    $seller->admin_to_pay = $seller->admin_to_pay + $orderDetail->price + $orderDetail->tax + $orderDetail->shipping_cost;
                    $seller->save();
                }
            }
        }
        foreach ($order->orderDetails as $detail){
            $token = @$detail->product->user->fcm_token;
            if ($token) {
                $this->noti('هناك طلبية جديدة','يرجي الاطلاع علي قائمة الطلبات هناك طلبية جديدة',$token);
            }
        }
        $order->commission_calculated = 1;
        $order->save();
        
       
        if($test == 1){
              $club_point = new ClubPoint;
        $club_point->user_id = $order->user_id;
        $club_point->points = 0;
        foreach ($order->orderDetails as $key => $orderDetail) {
            $total_pts = ($orderDetail->product->earn_point) * $orderDetail->quantity;
            $club_point->points += $total_pts;
        }
        $club_point->convert_status = 0;
        $club_point->save();
        $club_point_convert_rate = BusinessSetting::where('type', 'club_point_convert_rate')->first()->value;

        $club_point = ClubPoint::findOrFail($club_point->id);
        $wallet = new Wallet;
        $wallet->user_id =$id2;
        $wallet->amount = floatval($club_point->points / $club_point_convert_rate);
        $wallet->payment_method = 'Club Point Convert';
        $wallet->payment_details = 'Club Point Convert';
        $wallet->save();
        $user = User::find($id2);
        $user->balance = $user->balance + floatval($club_point->points / $club_point_convert_rate);
        $user->save();
        $club_point->convert_status = 1;
        $club_point->save();
        }
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        $array['view'] = 'emails.invoice';
        $array['subject'] = translate('Your order has been placed') . ' - ' . $order->code;
        $array['from'] = env('MAIL_USERNAME');
        $array['order'] = $order;
        if (env('MAIL_USERNAME') != null && $order->user_id != null) {
            try {
                Mail::to(json_decode($order->shipping_address)->email)->queue(new InvoiceEmailManager($array));
                Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new InvoiceEmailManager($array));
                Mail::to(User::where('id', $order->orderDetails->first()->seller_id)->first()->email)->queue(new InvoiceEmailManager($array));
                $user = User::where('id', $order->orderDetails->first()->seller_id)->first();
                $user->notify(new OrderSeller($order));
            } catch (\Exception $e) {
                return $e;
            }
        }
        if (App::getLocale() == 'en') {
            return view('payment_order_success_en',compact('order'));
        }
        return view('payment_order_success',compact('order'));
    }

    public function apishipingcan($order){}

    public function errorUrl()
    {
        flash(translate('error Occer'))->error();
        return redirect()->route('home');
    }

    public function successUrl()
    {
        if (Session::get('order_id') == null && Session::has('amount_pakege')) {
            $seller = Seller::where('user_id', Auth::id())->first();
            $seller->paid = 1;
            $seller->save();
            session()->forget('amount_pakege');
            session()->forget('seller_id');
            flash(translate('Your Shop has been created successfully!'))->success();
            $lang = Session()->get('locale');
            return redirect()->route('shops.index', ['lang' => $lang]);
        } elseif (Session::get('payment_type') == 'wallet_payment') {
            $walletController = new WalletController;
            return $walletController->wallet_payment_done(Session::get('payment_data'), 'thawani');
        } else {
            $checkoutController = new CheckoutController;
            $payment = 'thwani';
            flash(translate('success'))->success();
            return $checkoutController->checkout_done(Session::get('order_id'), $payment);
        }
    }
}
