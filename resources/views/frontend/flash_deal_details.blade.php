@extends('frontend.layouts.app')

@section('content')
@php
$slider_error = json_decode(get_setting('error_slider'), true); 
$error_panner = json_decode(get_setting('error_panner'), true); 
$error_product = json_decode(get_setting('error_product'), true); 
$color = App\BusinessSetting::where('type','base_color')->first()->value;
@endphp
    @if($flash_deal->status == 1 && strtotime(date('Y-m-d H:i:s')) <= $flash_deal->end_date) 
    <div style="background-color:{{ $flash_deal->background_color }}">
        <section class="text-center mb-5">
            <img
                src="{{ static_asset('assets/img/placeholder-rect.jpg') }}"
                data-src="{{ uploaded_asset($flash_deal->banner) }}"
                alt="{{ $flash_deal->title }}"
                class="img-fit w-100 lazyload"
            >
        </section>
        <section class="mb-4">
            <div class="container">
                <div class="text-center my-4 text-{{ $flash_deal->text_color }}">
                    <h1 class="h2 fw-600">{{ $flash_deal->title }}</h1>
                    <div class="aiz-count-down aiz-count-down-lg ml-3 align-items-center justify-content-center" data-date="{{ date('Y/m/d H:i:s', $flash_deal->end_date) }}"></div>
                </div>
                <div class="row gutters-5 row-cols-xxl-5 row-cols-lg-4 row-cols-md-3 row-cols-2">
                    @foreach ($flash_deal->flash_deal_products as $key => $flash_deal_product)
                        @php
                            $product = \App\Product::find($flash_deal_product->product_id);
                        @endphp
                        @if ($product->published != 0)
                            <div class="col mb-2">
                                <div class="aiz-card-box border border-primary rounded shadow-sm hov-shadow-md h-392px has-transition bg-white"style="  box-shadow: 3px 5px 7px #6b6167 !important;">
          <div class="position-relative "style="
                            border-bottom: 1px solid @php 
            echo App\BusinessSetting::where('type','base_color')->first()->value;
                    @endphp;
                        ">                                        <a href="{{ route('product', $product->slug) }}" class="d-block">
                                            <img
                                                class="img-fit lazyload mx-auto h-240px h-md-210px"
                                                src="{{ uploaded_asset($error_product)  }}"
                                                data-src="{{ uploaded_asset($product->thumbnail_img) }}"
                                                alt="{{  $product->getTranslation('name')  }}"
                                                onerror="this.onerror=null;this.src='{{ uploaded_asset($error_product)  }}';"
                                            >
                                        </a>
                                        <div class="absolute-top-right aiz-p-hov-icon">
                                            <a href="javascript:void(0)" onclick="addToWishList({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to wishlist') }}" data-placement="left">
                                                <i class="la la-heart-o"></i>
                                            </a>
                                            <a href="javascript:void(0)" onclick="addToCompare({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to compare') }}" data-placement="left">
                                                <i class="las la-sync"></i>
                                            </a>
                                            <a href="javascript:void(0)" onclick="showAddToCartModal({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to cart') }}" data-placement="left">
                                                <i class="las la-shopping-cart"></i>
                                            </a>
                                        </div>
                                    </div>
                                   <div class="p-md-3 p-2 text-left" >
                                <div class="fs-15" style="display: inline-flex;">
                                  <span class="fw-700 text-primary fs-6"  style="font-size: 14px;">{{ home_discounted_base_price($product->id) }}</span>
              @if(home_base_price($product->id) != home_discounted_base_price($product->id))
                                                    <del class="fw-600 opacity-50 " style="font-size: smaller  !important;margin: 3px;"> {{ home_base_price($product->id) }}</del>
                                                    @else
                                                   <del class="fw-600 opacity-50 " style="font-size: small  !important;margin: 3px; color:white">{{ home_base_price($product->id) }}</del>  
                                                   @endif
                                  
                                </div>
                               <div style="text-align: center;">
                                <div class="rating rating-sm mt-1 " style="display: inline-flex;text-align: center;">
                                    {{ renderStarRating($product->rating) }}
                                   
                                </div>
                                </div>
                              
                                   <h3 class="fw-600 fs-10 text-truncate-2 lh-1-4  h-2px" style="font-size: 16px !important;">
                                        <a href="{{ route('product', $product->slug) }}" class="d-block text-reset" style="height: 25px;"> {!!Str::limit($product->getTranslation('name'), 16)!!}</a>

                                    </h3> 
                               
                                     <div class="rounded px-2 mt-2 bg-soft-primary border-soft-primary border " style="text-align: center;">
                                                                       <a href="javascript:void(0)" onclick="showAddToCartModal({{ $product->id }})" data-toggle="tooltip" data-title="{{ translate('Add to cart') }}">{{ translate('Add to cart') }}</a>

                                    </div>

                                        @if (\App\Addon::where('unique_identifier', 'club_point')->first() != null && \App\Addon::where('unique_identifier', 'club_point')->first()->activated)
                                            <div class="rounded px-2 mt-2 bg-soft-primary border-soft-primary border">
                                                {{ translate('Club Point') }}:
                                                <span class="fw-700 float-right">{{ $product->earn_point }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    </div>
    @else
        <div style="background-color:{{ $flash_deal->background_color }}">
            <section class="text-center">
                <img src="{{ uploaded_asset($flash_deal->banner) }}" alt="{{ $flash_deal->title }}" class="img-fit w-100">
            </section>
            <section class="pb-4">
                <div class="container">
                    <div class="text-center text-{{ $flash_deal->text_color }}">
                        <h1 class="h3 my-4">{{ $flash_deal->title }}</h1>
                        <p class="h4">{{  translate('This offer has been expired.') }}</p>
                    </div>
                </div>
            </section>
        </div>
    @endif
@endsection
