<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Traits\ApiResponser;

class NotificationController extends Controller
{
    use ApiResponser;

    function __construct(){
        $this->middleware('permission:markiting', ['only' => ['index','store']]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.marketing.notifications.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $title = $request->title;
        $body = $request->body;
        $tokens = User::query()->pluck('fcm_token')->toArray();
        if (count($tokens) > 0){
            if(count($tokens) > 900){
                foreach (collect($tokens)->chunk(900) as $item){
                    $tokens = $item->toArray();
                    $this->noti($title,$body,$tokens);
                }
            }else{
                $this->noti($title,$body,$tokens);
            }
            flash('تم ارسال الاشعار بنجاح')->success();
            return back();
        }else{
            flash('لم يتم ارسال الاشعار')->danger();
            return back();
        }
    }
}
