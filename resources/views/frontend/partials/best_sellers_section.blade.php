@if (\App\BusinessSetting::where('type', 'vendor_system_activation')->first()->value == 1)
    @php
        $array = array();
        foreach ($sellers as $key => $seller) {
            if($seller->user != null && $seller->user->shop != null){
                $total_sale = 0;
                foreach ($seller->user->products as $key => $product) {
                    $total_sale += $product->num_of_sale;
                }
                $array[$seller->id] = $total_sale;
            }
        }
        asort($array);
    @endphp
    @if(!empty($array))
        <section class="mb-4">
        <div class="container">
            <div class="px-2 py-4 px-md-4 py-md-3 bg-white shadow-sm rounded">
                <div class="d-flex mb-3 align-items-baseline border-bottom">
                    <h3 class="h5 fw-700 mb-0">
                        <span class="border-bottom border-primary border-width-2 pb-3 d-inline-block">{{ translate('Best Sellers')}}</span>
                    </h3>
                    <a href="javascript:void(0)" class="ml-auto mr-0 btn btn-primary btn-sm shadow-md">{{ translate('Top 20') }}</a>
                </div>
                <div class="aiz-carousel gutters-10 half-outside-arrow" data-items="3" data-lg-items="3"  data-md-items="2" data-sm-items="2" data-xs-items="1" data-rows="2">
                    @php
                        $count = 0;
                        $slider_error = json_decode(get_setting('error_slider'), true); 
                        $error_panner = json_decode(get_setting('error_panner'), true); 
                        $error_product = json_decode(get_setting('error_product'), true); 
                         $color = App\BusinessSetting::where('type','base_color')->first()->value;
                    @endphp
                    @foreach ($array as $key => $value)
                        @if ($count < 20)
                            @php
                                $count ++;
                                $seller = \App\Seller::find($key);
                                $total = 0;
                                $rating = 0;
                                foreach ($seller->user->products as $key => $seller_product) {
                                    $total += $seller_product->reviews->count();
                                    $rating += $seller_product->reviews->sum('rating');
                                }
                            @endphp
                            <div class="carousel-box">
                                <div class="row no-gutters box-3 align-items-center border border-primary rounded hov-shadow-md my-2 has-transition" style="  box-shadow: 3px 5px 7px #6b6167 !important">
                                    <div class="col-4">
                                        <a href="{{ route('shop.visit', $seller->user->shop->slug) }}" class="d-block p-3">
                                            <img style=" height: 90px;width: 100%;"
                                                src="{{ uploaded_asset($error_product)  }}" hight="50"
                                                data-src="@if ($seller->user->shop->logo !== null) {{ uploaded_asset($seller->user->shop->logo) }}  @else {{ uploaded_asset($error_product)  }} @endif"
                                                alt="{{ $seller->user->shop->name }}"
                                                class="img-fluid lazyload"
                                            >
                                        </a>
                                    </div>
                                    <div class="col-8 border-left border-primary">
                                        <div class="p-3 text-left">
                                            <h2 class="h6 fw-600 text-truncate">
                                                <a href="{{ route('shop.visit', $seller->user->shop->slug) }}" class="text-reset">{{ $seller->user->shop->shop_name() }}</a>
                                            </h2>
                                            <div class="rating rating-sm mb-2">
                                                @if ($total > 0)
                                                    {{ renderStarRating($rating/$total) }}
                                                @else
                                                    {{ renderStarRating(0) }}
                                                @endif
                                            </div>
                                            <a href="{{ route('shop.visit', $seller->user->shop->slug) }}" class="btn btn-soft-primary btn-sm">
                                                {{ translate('Visit Store') }} <i class="las la-angle-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>
    @endif
@endif
