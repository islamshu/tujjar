<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Shop;
use App\User;
use App\Seller;
use App\BusinessSetting;
use Auth;
use Hash;
use App\ShopTranslation;
use App\Http\Controllers\ThawaniController;
use Validator;
use App\Vendorpackege;
use App\Notifications\EmailVerificationNotification;

class ShopController extends Controller
{

    public function __construct()
    {
        $this->middleware('user', ['only' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
       public function index(Request $request)
    {
        $shop = Auth::user()->shop;
        $lang = $request->lang;
        return view('frontend.user.seller.shop', compact('shop','lang'));
    }
    public function index2(Request $request){
        $shop = Auth::user()->shop;
    //    dd( $shop->getTranslation('name') );
        
        $lang = $request->lang;
        return view('frontend.user.seller.shop', compact('shop','lang')); 
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(Auth::check() && Auth::user()->user_type == 'admin'){
            flash(translate('Admin can not be a seller'))->error();
            return back();
        }
        else{
            if(Vendorpackege::count() != 0 ){
                 return view('frontend.vendor_packege');
            }else{
            return view('frontend.seller_form');
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
     
     public function store2(Request $request){
         session()->put('vendor_pakege',$request->packege_id);
            return view('frontend.seller_form');
     }
    public function store(Request $request)
    {
        
        $user = null;
        if(!Auth::check()){
            $validator =    Validator::make($request->all(), [
        
            'password' => 'required|min:6'
        ]);
        if ($validator -> fails()) {
              flash(translate('Password must be at least 6 characters'))->error();
                return back();
        }
            if(User::where('email', $request->email)->first() != null){
                flash(translate('Email already exists!'))->error();
                return back();
            }
            if(User::where('email', $request->email)->first() != null){
            flash(translate('Email already exists!'))->error();
            return back();
            }
             if(User::where('phone', $request->phone)->first() != null){
                flash(translate('Phone already exists!'))->error();
                return back();
            }
            
            
            if($request->password == $request->password_confirmation){
                $user = new User;
                $user->name = $request->name;
                $user->email = $request->email;
                $user->phone = $request->phone;
                

                $user->user_type = "seller";
                $user->password = Hash::make($request->password);
                $user->vendor_pakege_id = session()->get('vendor_pakege');

                $user->save();
                
            }
            else{
                flash(translate('Sorry! Password did not match.'))->error();
                return back();
            }
        }
        else{
            $user = Auth::user();
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
        $seller->vendor_pakege_id = session()->get('vendor_pakege');
        $seller->save();
        }
        if(Shop::where('user_id', $user->id)->first() == null){
            $shop = new Shop;
            $shop->user_id = $user->id;
            $shop->name = $request->name;
            $shop->name_ar = $request->name_ar;
            $shop->address = $request->address;
  

            $shop->vendor_pakege_id = session()->get('vendor_pakege');

            session()->forget('vendor_pakege');

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
                      $type = $user->shop->vendor_pakege_id;
                    
                  $price = Vendorpackege::find($type)->price;
        $session =session()->put('amount_pakege',$price);
        
       if($seller->paid != 1){
            $thawani = new ThawaniController;
            return $thawani->thawani($request);
       }
       else{
           flash(translate('Your Shop has been created successfully!'))->success();
           $lang = Session()->get('locale');
            return redirect()->route('shops.index',['lang'=>$lang]);
       }

              
            }
            else{
                $seller->delete();
                $user->user_type == 'customer';
                $user->save();
            }
        }

           flash(translate('Your Shop has been created successfully!'))->success();
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function update(Request $request, $id)
    {
        // dd($request);
      
        $shop = Shop::find($id);


        
        if($request->has('name') || $request->has('meta_title') || $request->has('meta_description') || $request->has('address')){
            $shop->name = $request->name;
            $shop->name_ar = $request->name_ar;

            $shop->address = $request->address;
            $shop->slug = preg_replace('/\s+/', '-', $request->name).'-'.$shop->id;
            $shop->meta_title = $request->name;
            $shop->meta_description = $request->meta_description;
            $shop->logo = $request->logo;
            $shop->save();
        
        

        
    }
    

            if ($request->has('shipping_cost')) {
                $shop->shipping_cost = $request->shipping_cost;
            }
           if ($request->has('logo')) {
                $shop->logo = $request->logo;
            } 

            if ($request->has('pick_up_point_id')) {
                $shop->pick_up_point_id = json_encode($request->pick_up_point_id);
            }
            else {
                $shop->pick_up_point_id = json_encode(array());
            }
         if ($request->has('sliders')) {
$shop->sliders = $request->sliders;            }

       if($request->has('facebook') ||  $request->has('twitter') || $request->has('youtube') || $request->has('instagram')){
            $shop->facebook = $request->facebook;
            $shop->instagram = $request->instagram;
            $shop->twitter = $request->twitter;
            $shop->youtube = $request->youtube;

        }

       
        if($shop->save()){
            flash(translate('Your Shop has been updated successfully!'))->success();
            return back();
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function verify_form(Request $request)
    {
        if(Auth::user()->seller->verification_info == null){
            $shop = Auth::user()->shop;
            return view('frontend.user.seller.verify_form', compact('shop'));
        }
        else {
            flash(translate('Sorry! You have sent verification request already.'))->error();
            return back();
        }
    }
  

    public function verify_form_store(Request $request)
    {
               

        $data = array();
        $i = 0;
            
        foreach (json_decode(BusinessSetting::where('type', 'verification_form')->first()->value) as $key => $element) {
            
        
 
            $item = array();
            if ($element->type == 'text') {
                $item['type'] = 'text';
                $item['label'] = $element->label;
                $item['value'] = $request['element_'.$i];
            }
            elseif ($element->type == 'select' || $element->type == 'radio') {
                $item['type'] = 'select';
                $item['label'] = $element->label;
                $item['value'] = $request['element_'.$i];
            }
            elseif ($element->type == 'multi_select') {
                $item['type'] = 'multi_select';
                $item['label'] = $element->label;
                $item['value'] = json_encode($request['element_'.$i]);
            }
            elseif ($element->type == 'file') {
                $item['type'] = 'file';
                $item['label'] = $element->label;
                $item['value'] = $request['element_'.$i]->store('uploads/verification_form');
               
            }
            array_push($data, $item);
            $i++;
        }
        $seller = Auth::user()->seller;
        $seller->verification_info = json_encode($data);
        
        if($seller->save()){


            flash(translate('Your shop verification request has been submitted successfully!'))->success();
            return redirect()->route('dashboard');
        }

        flash(translate('Sorry! Something went wrong.'))->error();
        return back();
    }
}
