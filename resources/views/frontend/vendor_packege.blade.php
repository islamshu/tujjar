@extends('frontend.layouts.app')

@section('content')
<section class="py-8 bg-primary text-white">
    <div class="container">
        <div class="row">
            <div class="col-xl-8 mx-auto text-center">
                <h1 class="mb-0 fw-700">{{ translate('Store subscription packages') }}</h1>
                <br>

            </div>
        </div>
    </div>
</section>

<section class="py-4 py-lg-5">
    <div class="container">
        <div class="row row-cols-xxl-4 row-cols-lg-3 row-cols-md-2 row-cols-1 gutters-10 justify-content-center">
            @foreach (\App\Vendorpackege::get() as $key => $vendor)
                <div class="col">
                    <div class="card overflow-hidden">
                        <div class="card-body">
                            <div class="text-center mb-4 mt-3">
                                <img class="mw-100 mx-auto mb-4" src="{{ uploaded_asset($vendor->image) }}" height="100">
                                    @if(\App\Language::where('code', Session::get('locale', Config::get('app.locale')))->first()->rtl == 1)

                                <h5  class="mb-3 h5 fw-600">{{$vendor->title}}</h5>
                                @else 
                                 <h5  class="mb-3 h5 fw-600">{{$vendor->title_en}}</h5>
                                  @endif

                            </div>
                           

                            <div class="mb-5 d-flex align-items-center justify-content-center">
                               
                                    <span class="display-4 fw-400 lh-1 mb-0" style="font-size:20px;color: red;">{{ single_price($vendor->price) }}<span class="fw-400" style="font-size:12px"> &nbsp;/&nbsp;{{ translate('Pay for first time') }}</span></span>
                              

                            </div>
                                                                                                @if(\App\Language::where('code', Session::get('locale', Config::get('app.locale')))->first()->rtl == 1)

                             <div class="text-center mb-4 mt-3" style="height: 270px;">

                                {!! $vendor->description !!}
                                @else
                                 <div class=" mb-4 mt-3" style="height: 270px;">
                                 {!! $vendor->dec_en !!}
                                @endif
                            </div>
                            <form style="text-align: center;" method ="get" action="{{route('shops.store2')}}">
                                @csrf
                         <input type="text" hidden name="packege_id" value="{{$vendor->id}}" >
                           <button class="btn btn-primary" type="submit"  >{{ translate('Register')}}</button>


                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection