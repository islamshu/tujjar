@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
    <h5 class="mb-0 h6">{{translate('Edit Color')}}</h5>
</div>

<div class="col-lg-6 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Color')}}</h5>
        </div>
        <div class="card-body">
            <form action="{{route('color.update', $color->id)  }}" method="POST">
            	@csrf
                @method('put')
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="name">{{translate('color')}}</label>
                    <div class="col-sm-9">
                        <input type="color" placeholder="{{translate('color')}}" value="{{ $color->color }}" id="color" name="color" class="form-control" required>
                    </div>
                </div>
              
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-primary">{{translate('Save')}}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
