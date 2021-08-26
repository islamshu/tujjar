<?php

namespace App\Http\Controllers;

use App\Colorcard;
use Illuminate\Http\Request;

class ColorcardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      return view('backend.color.index')->with('colors',Colorcard::paginate(20));  
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.color.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $color = new Colorcard();
        $color->color = $request->color;
        $color->save();
        flash(translate('Your Shop has been created successfully!'))->success();
        return redirect()->route('color.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Colorcard  $colorcard
     * @return \Illuminate\Http\Response
     */
    public function show(Colorcard $colorcard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Colorcard  $colorcard
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $color = Colorcard::find($id);
    //   dd($color);
        return view('backend.color.edit')->with('color',$color);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Colorcard  $colorcard
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $color = Colorcard::find($id);
        $color->color = $request->color;

        $color->save();
        flash(translate('Your Shop has been created successfully!'))->success();
        return redirect()->route('color.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Colorcard  $colorcard
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $color = Colorcard::find($id);
   
        $color->delete();
        flash(translate('deleted successfully!'))->success();
                return redirect()->route('color.index');



    }
}
