@extends('backend.layouts.app')

@section('content')

    <div class="row">
    	<div class="col-lg-8 mx-auto">
    		<div class="card">
    			<div class="card-header">
    				<h6 class="fw-600 mb-0">{{ translate('General') }}</h6>
    			</div>
    			<div class="card-body">
    				<form action="{{ route('update_shipping') }}" method="POST">
    					@csrf
                    	<div class="form-group row">
    						<label class="col-md-3 col-from-label">{{ translate('Shipping Cost') }}</label>
                            <div class="col-md-8">
        					 <div class="col-md-8">
        						<!--<input type="hidden" name="types[]" value="shipping_cost">-->
        						<input type="text" class="form-control" placeholder="{{ translate('Shipping Cost') }}" name="shipping_cost" value="{{ get_setting('shipping_cost') }}">
                            </div>
        				
                            </div>
    					</div>
    					<!--	<div class="form-group row">-->
    					<!--	<label class="col-md-3 col-from-label">{{ translate('Tax') }}</label>-->
         <!--                   <div class="col-md-8">-->
        	<!--				 <div class="col-md-8">-->
        	<!--					<input type="hidden" name="types[]" value="tax">-->
        	<!--					<input type="text" class="form-control" placeholder="{{ translate('Tax') }}" name="tax" value="{{ get_setting('tax') }}">-->
         <!--                   </div>-->
        				
         <!--                   </div>-->
    					<!--</div>-->
    					  <div class="form-group row">
                    <label class="col-lg-3 col-from-label">{{translate('Tax')}}</label>
                    <div class="col-md-4">
        						<!--<input type="hidden" name="types[]" value="tax">-->
        						<input type="text" class="form-control" placeholder="{{ translate('Tax') }}" name="tax" value="{{ get_setting('tax') }}">
                            </div>
                    <div class="col-lg-3">
                      <!--<input type="hidden" name="types[]" value="tax_type">-->
                        <select class="form-control aiz-selectpicker" name="tax_type" required>
                        	<option value="amount" <?php if(  get_setting('tax_type')  == 'amount') echo "selected";?> >{{translate('Flat')}}</option>
                        	<option value="percent" <?php if(  get_setting('tax_type') == 'percent') echo "selected";?> >{{translate('Percent')}}</option>
                        </select>
                    </div>
                </div>
    					<div class="text-right">
    						<button type="submit" class="btn btn-primary">{{ translate('Update') }}</button>
    					</div>
                    </form>
    			</div>
    		</div>
    
    	</div>
    </div>

@endsection
