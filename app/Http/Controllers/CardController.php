<?php

namespace App\Http\Controllers;

use App\Card;
use Illuminate\Http\Request;
use Auth;
use Validator;

class CardController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:business cards', ['only' => ['index','store']]);
        $this->middleware('permission:business cards', ['only' => ['create','store']]);
        $this->middleware('permission:business cards', ['only' => ['edit','update']]);
        $this->middleware('permission:business cards', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        return view ('backend.cards.index')->with('cards',Card::paginate(25));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function front(){
        $shop = Auth::user()->shop;

        return view('frontend.user.seller.card', compact('shop'));
    }
    public function store(Request $request)
    {
        $user = Auth::id();
         
        if(Card::where('user_id',$user)->first()){
            flash(translate('Your request all ready sent'))->error();
            return redirect()->back();

        }
 $validator = Validator::make($request->all(), [
            'logo'=>'required',
            'email'=>'required|email',
            'phone'=>'required|numeric',   
            'shop_name_ar'=>'required',
            'color'=>'required',
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
        //   flash( $errors)->error();

        flash(translate('Please verify all entered data'))->error();

        return redirect()->back();
            }      
            $rquest_all = $request->all();
             $rquest_all['user_id']=$user;
             
     
      
            Card::create($rquest_all);
        flash(translate('card has been inserted successfully'))->success();
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Card  $card
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $card = Card::find($id);

        $card->veiw = 1;
        $card->save();
        return view ('backend.cards.show')->with('card',$card);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Card  $card
     * @return \Illuminate\Http\Response
     */
    public function edit(Card $card)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Card  $card
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Card $card)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Card  $card
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $card = Card::find($id);
        $card->delete();
        return redirect()->route('cards.index');
        
    }
}
