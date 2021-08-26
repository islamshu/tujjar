@extends('backend.layouts.app')

@section('content')
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0 h6">{{translate('Coupon Information Adding')}}</h5>
            </div>
            <div class="card-body">
              <form class="form-horizontal" action="{{ route('notification.store') }}" method="POST">
                @csrf
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="name">{{translate('Notification title')}}</label>
                    <div class="col-lg-9">
                        <input class="form-control" type="text" name="title">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-lg-3 col-from-label" for="name">{{translate('Notification body')}}</label>
                    <div class="col-lg-9">
                        <textarea class="form-control" name="body" id="body" rows="4"></textarea>
                    </div>
                </div>
                <div id="coupon_form">
                </div>
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('btn Send')}}</button>
                </div>
              </from>
            </div>
        </div>
    </div>
@endsection
