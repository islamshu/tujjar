@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Packeges')}}</h1>
		</div>
		<div class="col-md-6 text-md-right">
			<a href="{{ route('vendpackeges.create') }}" class="btn btn-circle btn-info">
				<span>{{translate('Add New Packege')}}</span>
			</a>
		</div>
	</div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0 h6">{{translate('Packeges')}}</h5>
    </div>
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
                <tr>
                    <th width="10%">#</th>
                    <th>{{translate('title')}}</th>
                    <th>{{translate('price')}}</th>
                    <th>{{translate('image')}}</th>
                   
                    <th width="10%">{{translate('Options')}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vendores as $key => $vendore)
                  
                        <tr>
                            <td>{{ ($key+1) + ($vendores->currentPage() - 1)*$vendores->perPage() }}</td>
                            <td>{{$vendore->title}}</td>
                            <td>{{$vendore->price}}</td>
                               <td>
                            <a >
								<div class="form-group row">
									<div class="col-md-6">
										<img src="{{ uploaded_asset($vendore->image)}}" alt="Image" class="w-100px">
									</div>
								
								</div>
							</a>
                        </td>
                            <td class="text-right">
		                            <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('vendpackeges.edit', encrypt($vendore->id))}}" title="{{ translate('Edit') }}">
		                                <i class="las la-edit"></i>
		                            </a>
		                            
		                            
		                            
		                        <form style="display: inline" action="{{route('vendpackeges.destroy', $vendore->id)}}" method="post">
					@csrf @method('delete')
					<button type="submit" class="btn btn-soft-danger btn-icon btn-circle btn-sm delete-confirm">  <i class="las la-trash"></i></button>
				</form>
		                        </td>
                        </tr>
                 
                @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
            {{ $vendores->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection


@endsction
@section('modal')
    @include('modals.delete_modal')
@endsection
@section('script')
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
 $('.delete-confirm').click(function(event) {

      var form =  $(this).closest("form");
      var name = $(this).data("name");
      event.preventDefault();
      swal({
          title: `هل متأكد من حذف العنصر ؟`,
        icon: "warning",
          buttons: true,
          dangerMode: true,
      })
      .then((willDelete) => {
        if (willDelete) {
          form.submit();
        }
      });
  });
  </script>
  @endsection