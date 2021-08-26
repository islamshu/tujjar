@extends('backend.layouts.app')

@section('content')

    <div class="card">
        <div class="card-header">
          <h1 class="h2">{{ translate('Card Details') }}</h1>
        </div>
        <div class="card-header row gutters-5">
  			<div class="col text-center text-md-left">
  			</div>
             
         
  		</div>
    	<div class="card-body">
        <div class="card-header row gutters-6">
  			<div class="col text-center text-md-left">
            <address>
                <strong class="text-main"></strong><br>


                {{ translate('Shop name') }} :     <a href="/shop/{{ $card->user->shop->slug }}"> {{$card->shop_name_ar}} </a> <br>
                <br>
                
                {{translate('logo')}} : 	<img src="{{ uploaded_asset($card->logo) }}" alt="{{translate('Brand')}}" class="h-80px">
                <br>
                <br>
                {{ translate('Email') }} :       {{$card->email}}<br>
                <br>
                {{translate('User phone')}} : 	{{ $card->phone }}<br>
                <br>
                {{translate('business card color')}} : 	<input type="text" redeonly value="{{$card->color}}" style="background:{{$card->color}}"><br>
                <br>
                {{translate('Shop Address')}} : 	{{ $card->address}}<br>
               
                
            </address>
            
            
  
  				</div>
  				
    </div>
    
@endsection


