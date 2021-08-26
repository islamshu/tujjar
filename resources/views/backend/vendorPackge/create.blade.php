@extends('backend.layouts.app')

@section('content')

<div class="col-lg-12 mx-auto">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0 h6">{{translate('Vendor packeges')}}</h5>
        </div>

        <form class="form-horizontal" action="{{ route('vendpackeges.store') }}" method="POST" enctype="multipart/form-data">
        	@csrf
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="name">{{translate('Name ar')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Name ar')}}" id="name" name="title" class="form-control" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="name">{{translate('Name en')}}</label>
                    <div class="col-sm-9">
                        <input type="text" placeholder="{{translate('Name en')}}" id="name" name="title_en" class="form-control" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="email">{{translate('Price')}}</label>
                    <div class="col-sm-9">
                        <input type="number" placeholder="{{translate('Price')}}" id="email" name="price" class="form-control" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="mobile">{{translate('Description ar')}}</label>
                    <div class="col-sm-9">
						<textarea class="aiz-text-editor" name="description"></textarea>
                    </div>
                </div>
                 <div class="form-group row">
                    <label class="col-sm-3 col-from-label" for="mobile">{{translate('Description en')}}</label>
                    <div class="col-sm-9">
						<textarea class="aiz-text-editor" name="dec_en"></textarea>
                    </div>
                </div>
                       <div class="form-group row">
            <label class="col-md-3 col-form-label" for="signinSrEmail">{{translate('Image')}}</label>
            <div class="col-md-8">
                <div class="input-group" data-toggle="aizuploader" data-type="image">
                    <div class="input-group-prepend">
                        <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse')}}</div>
                    </div>
                    <div class="form-control file-amount">{{ translate('Choose File') }}</div>
                    <input type="hidden" name="image" class="selected-files">
                </div>
                <div class="file-preview box sm">
                </div>
            </div>
        </div>
                
                <div class="form-group mb-0 text-right">
                    <button type="submit" class="btn btn-sm btn-primary">{{translate('Save')}}</button>
                </div>
            </div>
        </form>

    </div>
</div>

@endsection
