@extends('frontend.layouts.app')

@section('content')

    <section class="py-5">
        <div class="container">
            <div class="d-flex align-items-start">
                @include('frontend.inc.user_side_nav')

                <div class="aiz-user-panel">

                    <div class="aiz-titlebar mt-2 mb-4">
                      <div class="row align-items-center">
                        <div class="col-md-6">
                            <h1 class="h3">{{ translate('Card Settings')}}
                                <a href="{{ route('shop.visit', $shop->slug) }}" class="btn btn-link btn-sm" target="_blank">({{ translate('Visit Shop')}})<i class="la la-external-link"></i>)</a>
                            </h1>
                        </div>
                      </div>
                    </div>

              @php
              $card = App\Card::where('user_id',auth()->id())->first();
              @endphp
              @if($card)
              <div class="card">
          <div class="card-body">
                                <table class="table aiz-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ translate('Shop Name') }}</th>
                                            <th>{{ translate('User Name') }}</th>
                                            <th>{{ translate('Phone') }}</th>
                                            <th>{{ translate('Email') }}</th>
                                            <th>{{ translate('Shop Logo') }}</th>
                                            <th>{{ translate('Color') }}</th>
                                            <th>{{ translate('Options')}}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                          <th>{{ $card->shop_name_ar }}</th>
                                            <th>{{ auth()->user()->name}}</th>
                                            <th>{{ $card->phone }}</th>
                                            <th>{{ $card->email }}</th>
                                            <th><img src="{{ uploaded_asset($card->logo)}}" width="100" hight="50" alt=""></th>
                                            <th>
                                            <div style="background:{{ @App\Color::find($card->color)->code }};text-align:center">
                                                <p>color</p>
                                            </div>
                                            </th>
                                             <td class="text-right">
                                                 
                                                     <form method="post" action="{{route('cards2.destroy',$card->id)}}">
                                                        @csrf
                                                        @method('delete')
                                                     <button type="submit" class="btn btn-soft-danger btn-icon btn-circle btn-sm">
                                                         <i class="las la-trash"></i>
                                                     </button>
                                                                
                                                     
                                                    </form>                                                    
                                                    </td>
                                    </tbody>

                                </table>
                                                           </div>             
 
                            </div>             
                            @endif        
                    <div class="card">
                     
                        <div class="card-body">
                            <form class="" action="{{ route('cards2.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <label class="col-md-2 col-form-label">{{ translate('Shop Name') }}<span class="text-danger text-danger">*</span></label>
                                    <div class="col-md-10">
                                        
                                        <input type="text" class="form-control mb-3" placeholder="{{ translate('Shop Name')}}" readonly name="shop_name_ar" value="{{ $shop->name_ar }}" >
                                    </div>
                                </div>
                                <!--<div class="row">-->
                                <!--    <label class="col-md-2 col-form-label">{{ translate('User Name') }}<span class="text-danger text-danger">*</span></label>-->
                                <!--    <div class="col-md-10">-->
                                <!--        <input type="text" class="form-control mb-3" placeholder="{{ translate('User Name')}}"  name="name" value="{{ $shop->user->name }}" required>-->
                                <!--    </div>-->
                                <!--</div>-->
                                
                                <div class="row">
                                    <label class="col-md-2 col-form-label">{{ translate('Phone') }}<span class="text-danger text-danger">*</span></label>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control mb-3" placeholder="{{ translate('Phone')}}"  name="phone" value="{{ $shop->user->phone }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <label class="col-md-2 col-form-label">{{ translate('Email') }}<span class="text-danger text-danger">*</span></label>
                                    <div class="col-md-10">
                                        <input type="email" class="form-control mb-3" placeholder="{{ translate('Email')}}"  name="email" value="{{ $shop->user->email }}" required>
                                    </div>
                                </div>
                              
                                <div class="row mb-3">
                                    <label class="col-md-2 col-form-label">{{ translate('Shop Logo') }}  <span class="text-danger text-danger">*</span></label>
                                    <div class="col-md-10">
                                        <div class="input-group" required data-toggle="aizuploader" data-type="image">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                                            </div>
                                            <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                                            <input type="hidden" name="logo" value="{{ $shop->user->shop->logo}}" class="selected-files" required>
                                        </div>
                                        <div class="file-preview box sm">
                                        </div>
                                    </div>
                                </div>
                                
                               

                                <div class="row">
                                    <label class="col-md-2 col-form-label">{{ translate('Select Color') }} <span class="text-danger text-danger">*</span></label>
                                                                                @foreach (App\Colorcard::get() as $item)

                                       <div class="col-3 col-md-3">
                                                <label class="aiz-megabox d-block mb-3">
                                                    <input value="{{$item->id}}" class="online_payment" type="radio" name="color" checked>
                                                    <span  class="d-block p-3 aiz-megabox-elem">
                                                        <span style="background:{{ $item->color}}"  class="d-block text-center">
                                                            <span  class="d-block fw-600 fs-15">{{ $item->color}}</span>
                                                        </span>
                                                    </span>
                                                </label>
                                            </div>
                                            @endforeach
                                </div>

                                
                                <div class="form-group mb-0 text-right">
                                    <button type="submit" class="btn btn-sm btn-primary">{{translate('Create Card')}}</button>
                                </div>
                            </form>
                        </div>
                    </div>

             
                </div>
            </div>
        </div>
    </section>

@endsection
