<!-- Top Bar -->
<div class="top-navbar bg-white border-bottom border-soft-secondary z-1035">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col">
                <ul class="list-inline d-flex justify-content-between justify-content-lg-start mb-0">
                    @if(get_setting('show_language_switcher') == 'on')
                    <li class="list-inline-item dropdown " id="lang-change">
                        @php
                            if(Session::has('locale')){
                                $locale = Session::get('locale', Config::get('app.locale'));
                            }
                            else{
                                $locale = 'en';
                            }
                        @endphp
                        @php
$slider_error = json_decode(get_setting('error_slider'), true); 
$error_panner = json_decode(get_setting('error_panner'), true); 
$error_product = json_decode(get_setting('error_product'), true); 
 @$dir = \App\Language::where('code',$locale)->first()->rtl;

@endphp
                        <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2" data-toggle="dropdown" data-display="static">
                            <img src="{{ uploaded_asset($error_product)  }}" data-src="{{ static_asset('assets/img/flags/'.$locale.'.png') }}" class=" lazyload" alt="{{ \App\Language::where('code', $locale)->first()->name }}" height="11">
                            <span class="opacity-60">{{ \App\Language::where('code', $locale)->first()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-left">
                            @foreach (\App\Language::all() as $key => $language)
                                <li>
                                    <a href="javascript:void(0)" data-flag="{{ $language->code }}" class="dropdown-item @if($locale == $language) active @endif">
                                        <img src="{{ uploaded_asset($error_product)  }}" data-src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" class="mr-1 lazyload" alt="{{ $language->name }}" height="11">
                                        <span class="language">{{ $language->name  }}</span> 
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        
                    </li>
                    @endif

                 @if(get_setting('show_currency_switcher') == 'on')
                    <li class="list-inline-item dropdown" id="currency-change">
                        @php
                            if(Session::has('currency_code')){
                                $currency_code = Session::get('currency_code');
                            }
                            else{
                                $currency_code = \App\Currency::findOrFail(\App\BusinessSetting::where('type', 'system_default_currency')->first()->value)->code;
                            }
                        @endphp
                        <a href="javascript:void(0)" class="dropdown-toggle text-reset py-2 opacity-60" data-toggle="dropdown" data-display="static">
                            {{ (\App\Currency::where('code', $currency_code)->first()->symbol) }}
                        </a>
                        <ul class="dropdown-menu  dropdown-menu-lg-left" style="text-align: center">
                            @foreach (\App\Currency::where('status', 1)->get() as $key => $currency)
                                <li>
                                    <a class="dropdown-item @if($currency_code == $currency->code) active @endif" href="javascript:void(0)" data-currency="{{ $currency->code }}">{{ $currency->name }} ({{ $currency->symbol }})</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endif
                         
                </ul>
            </div>
     

            <div class="col-8 text-right  d-lg-block">
                <ul class="list-inline mb-0">
                    @auth
                        @if(isAdmin())
                            <li class="list-inline-item"style="margin:5px">
                                <a href="{{ route('admin.dashboard') }}" class="text-reset  d-inline-block opacity-60">{{ translate('My Panel')}}</a>
                            </li>
                        @else
                            <li class="list-inline-item ">
                                <a href="{{ route('dashboard') }}" class="text-reset  d-inline-block opacity-60">{{ translate('My Panel')}}</a>
                            </li>
                        @endif
                        <li class="list-inline-item">
                            <a href="{{ route('logout') }}" class="text-reset  d-inline-block opacity-60">{{ translate('Logout')}}</a>
                        </li>
                    @else

                        <li class="list-inline-item">
                            <a href="{{ route('user.registration') }}" class="text-reset py-2  d-inline-block opacity-60">{{ translate('Registration')}}</a>
                        </li>
                                                <li class="list-inline-item " style="margin:0px">
                            <a href="{{ route('user.login') }}" class="text-reset py-33 d-inline-block opacity-60">{{ translate('Login')}}</a>
                        </li>
                    @endauth
                     @if (get_setting('vendor_system_activation') == 1)
                  @php
                  $color = App\BusinessSetting::where('type','base_color')->first()->value;

                  @endphp
                            @guest

                  <li class="list-inline-item btn btn-primary btn-sm shadow-md " style="
    padding: 5px;
" >
                            <a href="{{ route('shops.create') }}" style="color:white !important ;   "    class="text-reset  d-inline-block opacity-60">{{ translate('Be a Seller') }}</a>
                        </li>
                        @else
                        @php
                        $id = Auth::id();
                        $is_seller = \App\Seller::where('user_id',$id)->first();
                        @endphp
                       
                        @if(Auth::User()->user_type == 'seller' && Auth::User()->banned == 0 &&  $is_seller != null  )
                        @else
                        <li class="list-inline-item">
                            <a href="{{ route('shops.create') }}" style="color:{{$color}} !important ;font-weight: 700; @if($dir == 1 ) font-size:15px @else font-size:15px @endif "    class="text-reset py-2 d-inline-block opacity-60">{{ translate('Be a Seller') }}</a>
                        </li>
                        @endif
                        @endguest
                  @endif
                    </li>

                
                </ul>
            </div>
        </div>
        </div>
    </div>
<!-- END Top Bar -->
<header class="@if(get_setting('header_stikcy') == 'on') sticky-top @endif z-1020 bg-white border-bottom shadow-sm">
    <div class="position-relative logo-bar-area">
        <div class="container">
            <div class="d-flex align-items-center">

                <div class="col-auto col-xl-3 pl-0 pr-3 d-flex align-items-center">
                    <a class="d-block py-20px  ml-0" href="{{ route('home') }}">
                        @php
                            $header_logo = get_setting('header_logo');
                        @endphp
                        @if($header_logo != null)
                            <img src="{{ uploaded_asset($header_logo) }}" alt="{{ env('APP_NAME') }}" class=" h-65px h-md-70px w-sm-100px w-md-200px " height="70" >
                        @else
                            <img src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}" class=" h-65px h-md-70px w-sm-100px w-md-200px" height="70"  >
                        @endif
                    </a>

                    @if(Route::currentRouteName() != 'home')
                        <div class="d-none d-xl-block align-self-stretch category-menu-icon-box ml-auto mr-0">
                            <div class="h-100 d-flex align-items-center" id="category-menu-icon">
                                <div class="dropdown-toggle navbar-light bg-light h-40px w-50px pl-2 rounded border c-pointer">
                                    <span class="navbar-toggler-icon"></span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="d-lg-none ml-auto mr-0">
                    <a class="p-2 d-block text-reset" href="javascript:void(0);" data-toggle="class-toggle" data-target=".front-header-search">
                        <i class="las la-search la-flip-horizontal la-2x"></i>
                    </a>
                </div>

                <div class="flex-grow-1 front-header-search d-flex align-items-center bg-white">
                    <div class="position-relative flex-grow-1">
                        <form action="{{ route('search') }}" method="GET" class="stop-propagation">
                            <div class="d-flex position-relative align-items-center">
                                <div class="d-lg-none" data-toggle="class-toggle" data-target=".front-header-search">
                                    <button class="btn px-2" type="button"><i class="la la-2x la-long-arrow-left"></i></button>
                                </div>
                                <div class="input-group">
                                    <input type="text" class="border-0 border-lg form-control" id="search" name="q" placeholder="{{translate('I am shopping for...')}}" autocomplete="off">
                                    <div class="input-group-append d-none d-lg-block">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="la la-search la-flip-horizontal fs-18"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="typed-search-box stop-propagation document-click-d-none d-none bg-white rounded shadow-lg position-absolute left-0 top-100 w-100" style="min-height: 200px">
                            <div class="search-preloader absolute-top-center">
                                <div class="dot-loader"><div></div><div></div><div></div></div>
                            </div>
                            <div class="search-nothing d-none p-3 text-center fs-16">

                            </div>
                            <div id="search-content" class="text-left">

                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-none d-lg-none ml-3 mr-0">
                    <div class="nav-search-box">
                        <a href="#" class="nav-box-link">
                            <i class="la la-search la-flip-horizontal d-inline-block nav-box-icon"></i>
                        </a>
                    </div>
                </div>

                <div class="d-none d-lg-block ml-3 mr-0">
                    <div class="" id="compare">
                        @include('frontend.partials.compare')
                    </div>
                </div>

                <div class="d-none d-lg-block ml-3 mr-0">
                    <div class="" id="wishlist">
                        @include('frontend.partials.wishlist')
                    </div>
                </div>

                <div class="d-none d-lg-block  align-self-stretch ml-3 mr-0" data-hover="dropdown">
                    <div class="nav-cart-box dropdown h-100" id="cart_items">
                        @include('frontend.partials.cart')
                    </div>
                </div>

            </div>
        </div>
        @if(Route::currentRouteName() != 'home')
        <div class="hover-category-menu position-absolute w-100 top-100 left-0 right-0 d-none z-3" id="hover-category-menu">
            <div class="container">
                <div class="row gutters-10 position-relative">
                    <div class="col-lg-3 position-static">
                        @include('frontend.partials.category_menu')
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
      <div class="bg-white border-top border-gray-200 py-1">
        <div class="container">
            
            
            @if($dir == 0 )
            @php
            @$menu_lable = \App\BusinessSetting::where('type', 'header_menu_labels')->first()->value;
            @$menu_link = \App\BusinessSetting::where('type', 'header_menu_links')->first()->value;
            @endphp
            @else
            @php
            @$menu_lable = \App\BusinessSetting::where('type', 'header_menu_ar_labels')->first()->value;
            @$menu_link = \App\BusinessSetting::where('type', 'header_menu_ar_links')->first()->value;
            @endphp
            @endif

            <ul class="list-inline mb-0 pl-0 mobile-hor-swipe text-center">
                

                @foreach (json_decode(@$menu_lable , @$menu_link)  as $key => $element)

                <li class="list-inline-item mr-0">

                    @php
                        @$color = App\BusinessSetting::where('type','base_color')->first()->value;
                      
                    @endphp
                 
            <a href="{{ @json_decode($menu_link)[$key]  }}"  @if(str_contains(url()->current(), @json_decode($menu_link)[$key])) style ="color:{{  $color }} !important;font-size: 18px !important;
                    font-weight: bold !important;" @endif
                    style="font-size: 16px !important;
                    font-weight: bold !important;"
                        
                
                        class="opacity-60 fs-14 px-3  py-2 d-inline-block fw-600 hov-opacity-100 text-reset">
                      {{$element  }}
                    </a>
                </li>
                @endforeach

         
                         
                                </ul>
        </div>
    </div>  
</header>



