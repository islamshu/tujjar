@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Sellers')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('color.create') }}" class="btn btn-circle btn-info">
				<span>{{translate('Add New color')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
            <tr>
                <th>#</th>
                <th  width="60%" >{{translate('color')}}</th>
             
                <th width="10%">{{translate('Options')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($colors as $key => $color)
                    <tr>
                        <td>{{ ($key+1) + ($colors->currentPage() - 1)*$colors->perPage() }}</td>
                        
                        <td style="background: {{ $color->color }};w "></td>
                        <td>
                          <div class="aiz-topbar-item ml-2">
                              <div class="align-items-stretch d-flex dropdown">
                                  <a class="dropdown-toggle no-arrow text-dark" data-toggle="dropdown" href="javascript:void(0);" role="button" aria-haspopup="false" aria-expanded="false">
                                      <span class="d-flex align-items-center">
                                          <span class="d-none d-md-block">
                                            <button type="button" class="btn btn-sm btn-dark">{{translate('Actions')}}</button>
                                          </span>
                                      </span>
                                  </a>
                                  <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-menu-xs">
                                   
                                        <a href="{{route('color.edit', $color->id)}}"  class="dropdown-item">
                                          {{translate('Edit')}}
                                          <i class="fa fa-check text-success" aria-hidden="true"></i>
                                        </a>
                                      {{--  <a href="{{route('color.destroy', $color->id)}}" class="dropdown-item confirm-delete" data-href="{{route('color.destroy', $color->id)}}" class="">  --}}
                                       <form style="display: inline" action="{{route('color.destroy', $color->id)}}" method="post">
                                         @csrf
                                         @method('delete')
                                         <button type="submit" class="dropdown-item confirm-delete"> {{translate('Delete')}}</button>
                                       </form>
                                       
                                      {{--  </a>  --}}
                                  </div>
                              </div>
                          </div>
                        </td>
                    </tr>
            @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
          {{ $colors->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection
