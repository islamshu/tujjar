@php
$slider_error = json_decode(get_setting('error_slider'), true); 
$error_panner = json_decode(get_setting('error_panner'), true); 
$error_product = json_decode(get_setting('error_product'), true); 
@endphp
@php
$color = App\BusinessSetting::where('type','base_color')->first()->value;
$ll = App\Language::where('code',Session()->get('locale'))->first();

@endphp
@if($ll)
    @if ($ll->rtl == 0)
        @php
            $dir = "rtl";
            $dir_x ="ltr"
        @endphp
        @else
        @php
        $dir = "ltr";
        $dir_x ="rtl"
        @endphp
    @endif
@else
    @php
        $dir = "ltr";
        $dir_x ="rtl"
    @endphp
@endif

<style>
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 6px {{$color}}; 
}

::-webkit-scrollbar-thumb {
    -webkit-box-shadow: inset 0 0 6px {{$color}}; 
}


</style>
<div class="aiz-category-menu bg-white rounded @if(Route::currentRouteName() == 'home') shadow-sm" @else shadow-lg" id="category-sidebar" @endif style="height: 95%;
    overflow: auto; direction:{{$dir}}">
    <div class="p-3 bg-soft-primary d-none d-lg-block rounded-top all-category position-relative text-left">
    
    <a href="{{ route('categories.all') }}" class="text-reset">
            <span class="d-none d-lg-inline-block">{{ translate('See All') }}  <<  </span>
        </a>
                        <span class="fw-600 fs-16 mr-3">{{ translate('Categories') }}</span>

    </div>
    <ul class="list-unstyled categories  py-2 mb-0 text-left" >
        @foreach (\App\Category::where('level', 0)->get(); as $key => $category)
            <li class="category-nav-element" data-id="{{ $category->id }}">
                <a href="{{ route('products.category', $category->slug) }}" class="text-truncate text-reset py-2 px-3 d-block">
                    <span class="cat-name">{{ $category->getTranslation('name') }}</span>
                   <img
                        class="cat-image lazyload mr-2 opacity-60"
                        src="{{ uploaded_asset($error_product)  }}"
                        data-src="{{ uploaded_asset($category->icon) }}"
                        width="16"
                        alt="{{ $category->getTranslation('name') }}"
                        onerror="this.onerror=null;this.src='{{ uploaded_asset($error_product)  }}';"
                    >
                </a>
                @if(count(\App\Utility\CategoryUtility::get_immediate_children_ids($category->id))>0)
                    <div class="sub-cat-menu c-scrollbar-light rounded shadow-lg p-4" style="direction : {{$dir_x}}">
                        <div class="c-preloader text-center absolute-center">
                            <i class="las la-spinner la-spin la-3x opacity-70"></i>
                        </div>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</div>
