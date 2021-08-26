<div class="modal-body p-4 added-to-cart">
    <div class="text-center text-success mb-4">
        <i class="las la-check-circle la-3x"></i>
        <h3>{{ translate('Item added to your cart!')}}</h3>
    </div>
    @php
$slider_error = json_decode(get_setting('error_slider'), true); 
$error_panner = json_decode(get_setting('error_panner'), true); 
$error_product = json_decode(get_setting('error_product'), true); 
@endphp
    <div class="media mb-4">
        <img src="{{ uploaded_asset($error_product)  }}" data-src="{{ uploaded_asset($product->thumbnail_img) }}" class="mr-3 lazyload size-100px img-fit rounded" alt="Product Image">
        <div class="media-body pt-3 text-left">
            <h6 class="fw-600">
                {{ $product->langname()  }}
            </h6>
            <div class="row mt-3">
                <div class="col-sm-2 opacity-60">
                    <div>{{ translate('Price')}}:</div>
                </div>
                <div class="col-sm-10">
                    <div class="h6 text-primary">
                        <strong>
                            {{ single_price(($data['price']+$data['tax'])*$data['quantity']) }}
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center">
        <button class="btn btn-outline-primary mb-3 mb-sm-0" data-dismiss="modal">{{ translate('Back to shopping')}}</button>
        <a href="{{ route('cart') }}" class="btn btn-primary mb-3 mb-sm-0">{{ translate('Proceed to Checkout')}}</a>
    </div>
</div>
