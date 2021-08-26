@extends('backend.layouts.app')

@section('content')

<div class="aiz-titlebar text-left mt-2 mb-3">
	<div class="row align-items-center">
		<div class="col-md-6">
			<h1 class="h3">{{translate('All Sellers')}}</h1>
		</div>
	
	</div>
</div>

<div class="card">
    
    <div class="card-body">
        <table class="table aiz-table mb-0">
            <thead>
            <tr>
                <th width="5%">#</th>
                <th  width="20%" >{{translate('shop name')}}</th>
                <th  width="10%" >{{translate('status')}}</th>
             
                <th width="20%">{{translate('Options')}}</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cards as $key => $card)
                    <tr>
                        <td>{{ ($key+1) + ($cards->currentPage() - 1)*$cards->perPage() }}</td>
                        
                        <td >{{ $card->shop_name_ar }}</td>
                      @if ($card->veiw)
                      <td ><span class="badge badge-inline badge-success">view</span></td>
                      @else
                      <td ><span class="badge badge-inline badge-danger">no view</span></td>

                      @endif
                      <td >
                          <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{route('cards.show', $card->id)}}" title="{{ translate('View') }}">
                                <i class="las la-eye"></i>
                            </a>
                         <form style="display:inline" method="post" action ="{{route('cards.destroy', $card->id)}}" >
                             @csrf @method('delete')
                             <button type="submit" class ="btn btn-soft-danger btn-icon btn-circle btn-sm" ><i class="las la-trash"></i></button>
                             
                         </form>
                           
                      </td>
                    </tr>
                    
                     
            @endforeach
            </tbody>
        </table>
        <div class="aiz-pagination">
          {{ $cards->appends(request()->input())->links() }}
        </div>
    </div>
</div>

@endsection
@section('modal')
    @include('modals.delete_modal')
@endsection

@section('script')
    <script type="text/javascript">

    </script>
@endsection