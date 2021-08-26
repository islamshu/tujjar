<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Vendorpackege;
class VendorPackegeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.vendorPackge.index')->with('vendores',Vendorpackege::paginate(20));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
             return view('backend.vendorPackge.create');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'=>'required',
            'price'=>'required',
            'description'=>'required',
            'title_en'>'required',
            'dec_en'>'required',
            'image'=>'required',
            ]);
        Vendorpackege::create($request->all());
         flash(translate($request->name.' created successfully'));
        return redirect()->route('vendpackeges.index');
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
        $id = decrypt($id);
        
              return view('backend.vendorPackge.edit')->with('vendor',Vendorpackege::find($id));
  
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
      $vendor = Vendorpackege::find($id);
    
      
        $vendor->update($request->all());
         flash(translate($request->name.' updated successfully'));
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
     $ven = Vendorpackege::findOrFail($id);
     $ven->delete();
 flash(translate('deleted successfully'))->success();
 return redirect()->route('vendpackeges.index');

    }
}
