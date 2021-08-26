@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col">
			<h1 class="h3">{{ translate('Website Header') }}</h1>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-md-8 mx-auto">
		<div class="card">
			<div class="card-header">
				<h6 class="mb-0">{{ translate('Header Setting') }}</h6>
			</div>
			<div class="card-body">
				<form action="{{ route('business_settings.update') }}" method="POST" enctype="multipart/form-data">
					@csrf
					<div class="form-group row">
	                    <label class="col-md-3 col-from-label">{{ translate('Header Logo') }}</label>
						<div class="col-md-8">
		                    <div class=" input-group " data-toggle="aizuploader" data-type="image">
		                        <div class="input-group-prepend">
		                            <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
		                        </div>
		                        <div class="form-control file-amount">{{ translate('Choose File') }}</div>
								<input type="hidden" name="types[]" value="header_logo">
		                        <input type="hidden" name="header_logo" class="selected-files" value="{{ get_setting('header_logo') }}">
		                    </div>
		                    <div class="file-preview"></div>
						</div>
	                </div>
                    <div class="form-group row">
						<label class="col-md-3 col-from-label">{{translate('Show Language Switcher?')}}</label>
						<div class="col-md-8">
							<label class="aiz-switch aiz-switch-success mb-0">
								<input type="hidden" name="types[]" value="show_language_switcher">
								<input type="checkbox" name="show_language_switcher" @if( get_setting('show_language_switcher') == 'on') checked @endif>
								<span></span>
							</label>
						</div>
					</div>
                    <div class="form-group row">
                        <label class="col-md-3 col-from-label">{{translate('Show Currency Switcher?')}}</label>
						<div class="col-md-8">
							<label class="aiz-switch aiz-switch-success mb-0">
								<input type="hidden" name="types[]" value="show_currency_switcher">
								<input type="checkbox" name="show_currency_switcher" @if( get_setting('show_currency_switcher') == 'on') checked @endif>
								<span></span>
							</label>
						</div>
					</div>
	                <div class="form-group row">
						<label class="col-md-3 col-from-label">{{translate('Enable stikcy header?')}}</label>
						<div class="col-md-8">
							<label class="aiz-switch aiz-switch-success mb-0">
								<input type="hidden" name="types[]" value="header_stikcy">
								<input type="checkbox" name="header_stikcy" @if( get_setting('header_stikcy') == 'on') checked @endif>
								<span></span>
							</label>
						</div>
					</div>

					<div class="border-top pt-3">
						<label class="">Header Nav Menu</label>
						
					</div>
					@php
					$menu_lable = \App\BusinessSetting::where('type', 'header_menu_labels')->first()->value;
					$menu_link = \App\BusinessSetting::where('type', 'header_menu_links')->first()->value;
					@endphp
					
					@foreach (json_decode($menu_lable , $menu_link)  as $key => $element)
					

					<div class="row gutters-5">
						<div class="col-4">
							<div class="form-group">
								<input type="text" class="form-control" placeholder="Label" name="header_menu_labels[]" value="{{  $element}}">
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<input type="text" class="form-control" placeholder="Link with http:// Or https://" name="header_menu_links[]" value="{{ json_decode($menu_link)[$key]  }}">
							</div>
						</div>
						<div class="col-auto">
							<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
								<i class="las la-times"></i>
							</button>
						</div>
					</div>
					@endforeach
					<div class="header-nav-menu">
						<input type="hidden" name="types[]" value="header_menu_labels">
						<input type="hidden" name="types[]" value="header_menu_links">
										
					</div>



					<button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more" data-content="<div class=&quot;row gutters-5&quot;>
						<div class=&quot;col-4&quot;>
							<div class=&quot;form-group&quot;>
								<input type=&quot;text&quot; class=&quot;form-control&quot; placeholder=&quot;Label&quot; name=&quot;header_menu_labels[]&quot;>
							</div>
						</div>
						<div class=&quot;col&quot;>
							<div class=&quot;form-group&quot;>
								<input type=&quot;text&quot; class=&quot;form-control&quot; placeholder=&quot;Link with http:// Or https://&quot; name=&quot;header_menu_links[]&quot;>
							</div>
						</div>
						<div class=&quot;col-auto&quot;>
							<button type=&quot;button&quot; class=&quot;mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger&quot; data-toggle=&quot;remove-parent&quot; data-parent=&quot;.row&quot;>
								<i class=&quot;las la-times&quot;></i>
							</button>
						</div>
					</div>" data-target=".header-nav-menu">
					Add New
				</button>
				
				
				
				
				
					<div class="border-top pt-3">
						<label class="">Header Nav Menu</label>
						
					</div>
					@php
					$menu_ar_lable = \App\BusinessSetting::where('type', 'header_menu_ar_labels')->first()->value;
					$menu_ar_link = \App\BusinessSetting::where('type', 'header_menu_ar_links')->first()->value;
					@endphp
					
					@foreach (json_decode($menu_ar_lable , $menu_ar_link)  as $key => $element)
					

					<div class="row gutters-5">
						<div class="col-4">
							<div class="form-group">
								<input type="text" class="form-control" placeholder="Label" name="header_menu_ar_labels[]" value="{{  $element}}">
							</div>
						</div>
						<div class="col">
							<div class="form-group">
								<input type="text" class="form-control" placeholder="Link with http:// Or https://" name="header_menu_ar_links[]" value="{{ json_decode($menu_ar_link)[$key]  }}">
							</div>
						</div>
						<div class="col-auto">
							<button type="button" class="mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger" data-toggle="remove-parent" data-parent=".row">
								<i class="las la-times"></i>
							</button>
						</div>
					</div>
					@endforeach
					<div class="header-nav-menu-ar">
						<input type="hidden" name="types[]" value="header_menu_ar_labels">
						<input type="hidden" name="types[]" value="header_menu_ar_links">
										
					</div>



					<button type="button" class="btn btn-soft-secondary btn-sm" data-toggle="add-more" data-content="<div class=&quot;row gutters-5&quot;>
						<div class=&quot;col-4&quot;>
							<div class=&quot;form-group&quot;>
								<input type=&quot;text&quot; class=&quot;form-control&quot; placeholder=&quot;Label&quot; name=&quot;header_menu_ar_labels[]&quot;>
							</div>
						</div>
						<div class=&quot;col&quot;>
							<div class=&quot;form-group&quot;>
								<input type=&quot;text&quot; class=&quot;form-control&quot; placeholder=&quot;Link with http:// Or https://&quot; name=&quot;header_menu_ar_links[]&quot;>
							</div>
						</div>
						<div class=&quot;col-auto&quot;>
							<button type=&quot;button&quot; class=&quot;mt-1 btn btn-icon btn-circle btn-sm btn-soft-danger&quot; data-toggle=&quot;remove-parent&quot; data-parent=&quot;.row&quot;>
								<i class=&quot;las la-times&quot;></i>
							</button>
						</div>
					</div>" data-target=".header-nav-menu-ar">
					Add New
				</button>


					<div class="text-right">
						<button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
					</div>
					
				</form>
			</div>
		</div>
	</div>
</div>

@endsection
