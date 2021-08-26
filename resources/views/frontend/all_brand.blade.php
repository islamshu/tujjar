@extends('frontend.layouts.app')

@section('content')
@php
$color = App\BusinessSetting::where('type','base_color')->first()->value;

@endphp
<section class="pt-4 mb-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 text-center text-lg-left">
                <h1 class="fw-600 h4"  style="color :{{$color}} ">{{ translate('All Brands') }}</h1>
            </div>
            <div class="col-lg-6">
                <ul class="breadcrumb bg-transparent p-0 justify-content-center justify-content-lg-end">
                    <li class="breadcrumb-item opacity-50">
                        <a class="text-reset" href="{{ route('home') }}">{{ translate('Home')}}</a>
                    </li>
                    <li class="text-dark fw-600 breadcrumb-item">
                        <a class="text-reset" href="{{ route('brands.all') }}">"{{ translate('All Brands') }}"</a>
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
     $color = App\BusinessSetting::where('type','base_color')->first()->value;
    @endphp
    <div class="container">
        <div class="bg-white shadow-sm rounded px-3 pt-3">
            <div class="row row-cols-xxl-6 row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-1 gutters-10">
                @foreach (\App\Brand::all() as $brand)
                    <div class="col text-center">
                        <a href="{{ route('products.brand', $brand->slug) }}" class="d-block p-3 mb-3 border border-primary rounded hov-shadow-md"style="box-shadow: 3px 5px 7px #6b6167 !important" >

                            <img src="{{ uploaded_asset($brand->logo) }}"     onerror="this.onerror=null;this.src='{{ uploaded_asset($error_product) }} ';" class="lazyload mx-auto h-70px mw-100"  alt="{{ $brand->getTranslation('name') }}">
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

@endsection
