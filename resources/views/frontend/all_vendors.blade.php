@extends('frontend.layouts.app')
@php
$ll = App\Language::where('code',Session()->get('locale'))->first();
                 $color = App\BusinessSetting::where('type','base_color')->first()->value;

@endphp
<style>




.rounded {
  border-radius: 1rem;
  height: 12rem;
  background-color: {{$color}};
  padding: 0.5rem;
  position: relative;
  width:80%;
  margin-bottom:6%;

}
.rounded-container {
  border-radius: inherit;
  border: 1px solid #fff;
  height: 100%;
  position: relative;
}
.rounded-container__overlay {
  background-image: url("https://media-cdn.tripadvisor.com/media/photo-s/17/75/3f/d1/restaurant-in-valkenswaard.jpg");
  background-size: cover;
  height: 100%;
  opacity: 0.05;
}
.rounded-container__content-left {
  position: absolute;
  top: 0;
  left: 45px;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #fff;
  flex-direction: column;
}
.rounded-container__content-right {
  position: absolute;
  top: 0;
  right: 45px;
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  color: #fff;
  flex-direction: column;
}
.rounded-container__content-left h3 {
     font-size: 25px;
}

.rounded-container__content-left .middle {
  display: flex;
}

.rounded-container__content-left .middle .stars .far,
.rounded-container__content-left .middle .stars .fas {
  margin: 0 2px;
}
.rounded-container__content-left .middle .stars .golden {
  color: #fadf0f;
}
.rounded-container__content-left .middle .stars .gray {
  color: #ccc;
}
.rounded-container__content-left .middle a {
  padding: 0 8px;
  border-top-left-radius: 15px;
  border-bottom-right-radius: 15px;
}
.rounded-container__content-left .location .fa-map-marked-alt {
  color: #639744;
  margin-left: 5px;
}
.rounded-container__content-right h3 {
     /*margin-right: 35px; */
}

.rounded-container__content-right .middle {
  display: flex;
}

.rounded-container__content-right .middle .stars .far,
.rounded-container__content-right .middle .stars .fas {
  margin: 0 2px;
}
.rounded-container__content-right .middle .stars .golden {
  color: #fadf0f;
}
.rounded-container__content-right .middle .stars .gray {
  color: #ccc;
}
.hh3 a {
  padding: 0 8px;
  border-top-left-radius: 15px;
  border-bottom-right-radius: 15px;
}
.rounded-container__content-right .location .fa-map-marked-alt {
  color: #639744;
  margin-left: 5px;
}
.rounded-circle {
  position: absolute;
  top: 0;
  right: -6rem;
  min-width: 12rem;
  height: 12rem;
  border: 10px solid #fff;
  border-radius: 50%;
  background:  {{$color}};
  padding: 1rem;
}
.rounded-circle__content {
  height: 100%;
  width: 100%;
  border-radius: 50%;
  border: 1px solid #fff;
  background-image: url("https://media-cdn.tripadvisor.com/media/photo-s/17/75/3f/d1/restaurant-in-valkenswaard.jpg");
  background-size: cover;
}
  .dir_right{
    width: 80%  ;

}
.dir_left{
      width: 80%  ;
}
.dirrr{
    margin-right:100%;
}
}
@media only screen and (max-width: 350px) {
  img.wessam{
      margin-right:70px !important;
  }
}
</style>
@section('content')
@php 
$color = App\BusinessSetting::where('type','base_color')->first()->value;

@endphp
<section class="pt-4 mb-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 text-center text-lg-left">
                <h1 class="fw-600 h4"  style="color :{{$color}} ">{{ translate('All Vendors') }}</h1>
            </div>
            <div class="col-lg-4 text-center text-lg-left">
                <div class=" bg-white">
                    <div class="position-relative flex-grow-1">
                        <form action="{{ route('seller_seacrh') }}" method="get" class="stop-propagation">
                            
                            <div class="d-flex position-relative align-items-center">
                                <div class="d-lg-none" data-toggle="class-toggle" data-target=".front-header-search">
                                    <button class="btn px-2" type="button"><i class="la la-2x la-long-arrow-left"></i></button>
                                </div>
                                <div class="input-group">
                                
                                    <input type="text" class="border-0 border-lg form-control" id="search" @if ($request != null) value="{{$request->name}}" @endif   name="name" placeholder="{{translate('Seller Name')}}" autocomplete="off">
                                    <div class="input-group-append  d-lg-block">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="la la-search la-flip-horizontal fs-18"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="/all_vendors">"{{ translate('All Vendors') }}"</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
<section class="mb-4">
  @php
                $slider_error = json_decode(get_setting('error_slider'), true); 
                $error_panner = json_decode(get_setting('error_panner'), true); 
                $error_product = json_decode(get_setting('error_product'), true); 
                $error_vendor = json_decode(get_setting('error_vendor'), true); 
                $lang = Session()->get('locale');

                 $color = App\BusinessSetting::where('type','base_color')->first()->value;
                @endphp
               
<div class="container">
  <div class="row">
        @foreach($vendors as $seller)
    <div class="col-12 col-md-4 col-xl-4" style="direction: rtl;">
      <div class="rounded @if($ll->rtl == 0) dir_left @else dir_right  @endif " style="@if($ll->rtl == 0) margin-left @else margin-right  @endif : 22%" >
        <div class="rounded-container">
            
            
            
            
        
  
            
          <div class="rounded-container__overlay" style="background-image: url(@if ($seller->user->shop->logo !== null) {{ uploaded_asset($seller->user->shop->logo) }} @else {{ uploaded_asset($error_vendor)  }} @endif);  background-size: 50%;
  height: 100%;
  opacity: 0.05;"></div>
          <div class="@if($ll->rtl == 0) rounded-container__content-left @else rounded-container__content-right  @endif ">
              @if($seller->verify == 1)
                            <img style="height: 43px; margin-{{$ll->rtl == 0 ? 'left':'right'}}: 50% !important;" class="wessam" src="{{asset('public/assets/img/insignia.png')}}" alt="">
                @endif
            <h3> {{ $seller->user->shop->shop_name() }}</h3> 


            <div class="middle">
                @php
                                            $seller_id = \App\Seller::find($seller->id);
                                            $total = 0;
                                            $rating = 0;
                                            foreach ($seller->user->products as $key => $seller_product) {
                                                $total += $seller_product->reviews->count();
                                                $rating += $seller_product->reviews->sum('rating');
                                            }
                                            @endphp
             <div class="rating rating-sm mb-1">
                                @if ($total > 0)
                                    {{ renderStarRating($rating/$total) }}
                                @else
                                    {{ renderStarRating(0) }}
                                @endif
                            </div>
                                           
            
            </div>
         
            <div class="location"><i class="fas fa-map-marked-alt"></i><span> {{ App\City2::find($seller->user->shop->address)->longName()}} </span></div>
                <div class="hh3">
                                       <br />
                                            <a class="btn " style="background: #86dfff;" href="{{ route('shop.visit', $seller->user->shop->slug) }}">{{ translate('Visit Store') }}</a>
                                             </div>
          </div>
        </div>
        
        <div class="rounded-circle @if($ll->rtl == 0) dirrr @endif " >

          <div class="rounded-circle__content" style="background-image: url(@if ($seller->user->shop->logo !== null) {{ uploaded_asset($seller->user->shop->logo) }} @else {{ uploaded_asset($error_vendor)  }} @endif);"></div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  <div class="aiz-pagination aiz-pagination-center mt-4">
                            {{ $vendors->links() }}
                        </div>
</div>
  
</section>

@endsection
