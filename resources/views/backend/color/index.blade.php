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
                <th width="5%">#</th>
                <th  width="20%" >{{translate('color')}}</th>
             
                <th width="10%">{{translate('Options')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($colors as $key => $color)
                    <tr>
                        <td>{{ ($key+1) + ($colors->currentPage() - 1)*$colors->perPage() }}</td>
                        
                        <td style="background: {{ $color->color }};w "></td>
                      <td class="text-right">
                          <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('color.edit', $color->id)}}" title="{{ translate('Edit') }}">
                                <i class="las la-edit"></i>
                            </a>
                         <form style="display:inline" method="post" action ="{{route('color.destroy', $color->id)}}" >
                             @csrf @method('delete')
                             <button type="submit" class ="btn btn-soft-danger btn-icon btn-circle btn-sm" ><i class="las la-trash"></i></button>
                             
                         </form>
                           
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
