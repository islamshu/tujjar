<?php

include('api/v3.php');

Route::prefix('v1/auth')->middleware(['changeLanguage','header_request_env'])->group(function () {
    Route::post('login', 'Api\AuthController@login');
    Route::post('signup', 'Api\AuthController@signup');
    Route::post('social-login', 'Api\AuthController@socialLogin');
    Route::post('password/create', 'Api\PasswordResetController@create');
    Route::post('password/reset', 'Api\PasswordResetController@reset');


    Route::middleware('auth:api')->group(function () {
        Route::get('logout', 'Api\AuthController@logout');
        Route::get('user', 'Api\AuthController@user');
        Route::post('password/change', 'Api\PasswordResetController@change');

    });
});

Route::prefix('v1')->middleware(['changeLanguage','header_request_env'])->group(function () {
    Route::get('get_home', 'Api\HomeController@getHome');

    Route::post('order_test', 'Api\OrderController@make_order');
    Route::apiResource('banners', 'Api\BannerController')->only('index');

    Route::get('brands/top', 'Api\BrandController@top');
    Route::apiResource('brands', 'Api\BrandController')->only('index');

    Route::apiResource('business-settings', 'Api\BusinessSettingController')->only('index');

    Route::get('categories/featured', 'Api\CategoryController@featured');
    Route::get('categories/home', 'Api\CategoryController@home');
    Route::apiResource('categories', 'Api\CategoryController')->only('index');
    Route::get('sub-categories/{id}', 'Api\SubCategoryController@index')->name('subCategories.index');

    Route::apiResource('colors', 'Api\ColorController')->only('index');

    Route::apiResource('currencies', 'Api\CurrencyController')->only('index');

    Route::apiResource('customers', 'Api\CustomerController')->only('show');

    Route::apiResource('general-settings', 'Api\GeneralSettingController')->only('index');

    Route::apiResource('home-categories', 'Api\HomeCategoryController')->only('index');


    Route::get('user/{id}', 'Api\AuthController@userinfo');

    Route::get('products/admin', 'Api\ProductController@admin');
    Route::get('products/seller', 'Api\ProductController@seller');
    Route::get('products/category/{id}', 'Api\ProductController@category')->name('api.products.category');
    Route::get('products/sub-category/{id}', 'Api\ProductController@subCategory')->name('products.subCategory');
    Route::get('products/sub-sub-category/{id}', 'Api\ProductController@subSubCategory')->name('products.subSubCategory');
    Route::get('products/brand/{id}', 'Api\ProductController@brand')->name('api.products.brand');
    Route::get('products/todays-deal', 'Api\ProductController@todaysDeal');
    Route::get('products/flash-deal', 'Api\ProductController@flashDeal')->name('api_flashdeals');
    Route::get('products/featured', 'Api\ProductController@featured');
    Route::get('products/best-seller', 'Api\ProductController@bestSeller');
    Route::get('products/related/{id}', 'Api\ProductController@related')->name('products.related');
    Route::get('products/top-from-seller/{id}', 'Api\ProductController@topFromSeller')->name('products.topFromSeller');
    Route::get('products/search', 'Api\ProductController@search');
    Route::post('products/variant/price', 'Api\ProductController@variantPrice');
    Route::get('products/home', 'Api\ProductController@home');
    Route::apiResource('products', 'Api\ProductController')->except(['store', 'update', 'destroy']);


    Route::get('reviews/product/{id}', 'Api\ReviewController@index')->name('api.reviews.index');
    Route::post('shops/create', 'Api\ShopController@create');
    Route::get('shop/user/{id}', 'Api\ShopController@shopOfUser')->name('api.shop');

    Route::get('shops/details/{id}', 'Api\ShopController@info')->name('shops.info');
    Route::get('shops/products/all/{id}', 'Api\ShopController@allProducts')->name('shops.allProducts');
    Route::get('pages/{id}', 'Api\SearchController@pages');


    Route::get('shops/products/top/{id}', 'Api\ShopController@topSellingProducts')->name('shops.topSellingProducts');
    Route::get('shops/products/featured/{id}', 'Api\ShopController@featuredProducts')->name('shops.featuredProducts');
    Route::get('shops/products/new/{id}', 'Api\ShopController@newProducts')->name('shops.newProducts');
    Route::get('shops/brands/{id}', 'Api\ShopController@brands')->name('shops.brands');
    Route::get('bestseller', 'Api\ShopController@bestseller');
    Route::get('get_whatsapp', 'Api\GeneralSettingController@get_phone_for_site');


    Route::get('sellers', 'Api\ShopController@seller');


    Route::apiResource('shops', 'Api\ShopController')->only('index');

    Route::apiResource('sliders', 'Api\SliderController')->only('index');


    Route::apiResource('settings', 'Api\SettingsController')->only('index');

    Route::get('policies/seller', 'Api\PolicyController@sellerPolicy')->name('policies.seller');
    Route::get('policies/support', 'Api\PolicyController@supportPolicy')->name('policies.support');
    Route::get('policies/return', 'Api\PolicyController@returnPolicy')->name('policies.return');
    Route::get('product/get_search/{att}', 'Api\SearchController@index');
    Route::post('fillter_search', 'Api\SearchController@fillter_search');
    Route::get('test_cokkies', 'Api\SearchController@test_cokkies');
    Route::get('test_cokkies2', 'Api\SearchController@test_cokkies2');

    Route::get('/thawani/wallet/cancel/{id}', 'ThawaniController@wallitapierror')->name('api.waliet.thawani.cancel');
    Route::get('/thawani/wallet/done/{id}', 'ThawaniController@wallitapidone')->name('api.waliet.thawani.done');
    Route::get('/thawani/shiping/cancel/{id}', 'ThawaniController@apishipingcan')->name('api.shiping.thawani.cancel');
    Route::get('/thawani/shiping/done/{order_id}/{id}/{id2}', 'ThawaniController@apishipingdone')->name('api.shiping.thawani.done');
    Route::get('/thawani/vendor/cancel/{id}', 'ThawaniController@vendorapierror')->name('api.vendor.thawani.cancel');
    Route::get('/thawani/vendor/done/{id}', 'ThawaniController@vendorapidone')->name('api.vendor.thawani.done');
    Route::get('get_color_card', 'Api\SellerController@get_color_card')->name('api.get_color_card');
    Route::get('get_governorate', 'Api\ColorController@get_governorate')->name('api.get_governorate');
    Route::get('get_states/{id}', 'Api\ColorController@get_states')->name('api.get_states');
    Route::get('get_all_size', 'Api\SearchController@get_all_size')->name('api.get_all_size');
    Route::get('get_all_fabrics', 'Api\SearchController@get_all_fabrics')->name('api.get_all_fabrics');


    Route::get('get_all_colors', 'Api\SearchController@get_all_colors')->name('api.get_all_colors');

    Route::get('get_all_langs', 'Api\SearchController@get_all_langs')->name('api.get_all_langs');
    Route::get('vendor_bakege', 'Api\SearchController@vendor_bakege')->name('api.vendor_bakege');
    Route::get('carts', 'Api\CartController@index');
    Route::get('get_count_cart', 'Api\CartController@get_count_cart');
    Route::post('carts/add', 'Api\CartController@add');
    Route::post('retrun_to_paid/{id}/{id2}', 'Api\CartController@make_order_id')->name('api.make_order');
    Route::post('carts/change-quantity', 'Api\CartController@changeQuantity');
    Route::apiResource('carts', 'Api\CartController')->only('destroy');
});

Route::prefix('v1')->middleware(['is_login', 'changeLanguage','header_request_env'])->group(function () {
    //  Route::get('/test', 'Api\PolicyController@test');

    Route::get('wallet/history', 'Api\WalletController@walletRechargeHistory');
    Route::get('wallet/balance', 'Api\WalletController@balance');
    Route::post('wallet/recharge', 'Api\WalletController@recharge');


    Route::post('product/review', 'Api\ReviewController@store');
    Route::get('notofication', 'Api\GeneralSettingController@get_noification');
    Route::get('get_noification/{id}', 'Api\GeneralSettingController@get_noification_single')->name('notfy_single');

    Route::get('user/info', 'Api\UserController@info');
    Route::post('user/info/update', 'Api\UserController@updateName');
    Route::post('user/info/change_image', 'Api\UserController@change_image');


    Route::get('user/shipping/address', 'Api\AddressController@addresses');
    Route::post('user/shipping/create', 'Api\AddressController@createShippingAddress');
    Route::get('user/shipping/set_defullt_address/{id}', 'Api\AddressController@set_defulf_address');
    Route::post('user/shipping/update/{id}', 'Api\AddressController@updateShippingAddress');
    Route::get('user/shipping/delete/{id}', 'Api\AddressController@deleteShippingAddress');
    Route::post('coupon/apply', 'Api\CouponController@apply');
    Route::post('payments/pay/stripe', 'Api\StripeController@processPayment');
    Route::post('payments/pay/paypal', 'Api\PaypalController@processPayment');
    Route::post('payments/pay/wallet', 'Api\WalletController@processPayment');
    Route::post('payments/pay/cod', 'Api\PaymentController@cashOnDelivery');
    Route::get('wishlist', 'Api\WishlistController@index');
    Route::post('addTowishlist/{id}', 'Api\WishlistController@addtowishlist');
    Route::post('removeFormwishlist/{id}', 'Api\WishlistController@removeFormwishlist');
    Route::post('wishlists/check-product', 'Api\WishlistController@isProductInWishlist');
    Route::post('wishlists/store', 'Api\WishlistController@store');
    Route::apiResource('wishlists', 'Api\WishlistController')->except(['update', 'show']);
    Route::get('purchase-history/{id}', 'Api\PurchaseHistoryController@index');
    Route::get('purchase-history-details/{id}', 'Api\PurchaseHistoryDetailController@index')->name('purchaseHistory.details');


    Route::get('shop/user', 'Api\ShopController@shopOfUser');
    Route::post('send-message', 'Api\ConversationController@create');
    Route::get('get_meesage', 'Api\ConversationController@get_meesage');
    Route::get('meesage/{id}', 'Api\ConversationController@message_id')->name('api.message_id');
    Route::post('replay_message', 'Api\ConversationController@replay');
    Route::get('club_points', 'Api\ClubPointController@index');
    Route::post('club_points/convert/{id}', 'Api\ClubPointController@convert_point_into_wallet_id')->name('api_convert_club');


    Route::post('compare/add', 'Api\CompareController@create');
    Route::get('compare', 'Api\CompareController@index');
    Route::get('compare_delete/{id}', 'Api\CompareController@delete')->name('api.delete_compare');
    Route::get('compare/reset', 'Api\CompareController@reste')->name('api.reset_compare');
    Route::post('send_ticket', 'Api\SupportTicketController@store');
    Route::post('ticket_replies', 'Api\SupportTicketController@ticket_replies');
    Route::get('tickets', 'Api\SupportTicketController@index');

    Route::get('seller/dashboard', 'Api\SellerController@home');
    Route::post('seller/createOrupdate/{attribute}', 'Api\ProductController@seller_product')->name('api.create_product');
    Route::post('edit_store', 'Api\SellerController@store_edit');
    Route::post('bank_setting', 'Api\SellerController@bank_setting');

    Route::post('shop/product_published/{id}', 'Api\ShopController@product_published')->name('shops.product_published');
    Route::post('shop/product_featured/{id}', 'Api\ShopController@product_featured')->name('shops.product_featured');
    Route::post('shop/delete_product/{id}', 'Api\ShopController@delete_product')->name('shops.delete_product');
    // Route::post('seller/orders', 'Api\ShopController@delete_product')->name('shops.delete_product');
    Route::get('shops/get_products', 'Api\ShopController@allProductsLogin')->name('shops.allProductsLogin');
    Route::get('seller/orders', 'Api\SellerController@get_orders')->name('api_order');
    Route::post('seller/get_orders_by_status', 'Api\SellerController@get_orders_status');


    Route::get('seller/ordersDetails/{id}', 'Api\SellerController@ordersDetails')->name('api.ordersDetails');
    Route::get('seller/orders_delete/{id}', 'Api\SellerController@orders_delete')->name('api.orders_delete');
    Route::get('seller/product_review', 'Api\SellerController@product_seller_review')->name('api.product_review');

    Route::get('seller/PurchaseHistory', 'Api\SellerController@PurchaseHistory')->name('api.PurchaseHistory');
    Route::post('seller/Seller_card', 'Api\SellerController@Seller_card')->name('api.Seller_card');
    Route::post('seller_withdrow', 'Api\SellerController@seller_withdrow')->name('api.seller_withdrow');
    Route::get('Pending_Balance_for_seller', 'Api\SellerController@Pending_Balance_for_seller')->name('api.Pending_Balance_for_seller');
    Route::get('seller_withdraw_requests', 'Api\SellerController@seller_withdraw_requests')->name('api.seller_withdraw_requests');

    Route::get('seller/payments', 'Api\SellerController@payments')->name('api.seller_payments');
    Route::post('seller/update_delivery_status', 'Api\SellerController@update_delivery_status')->name('api.update_delivery_status');
    Route::post('seller/paid_to_be_vendor', 'Api\ShopController@piad_for_vendor')->name('api.paid_to_be_vendor');
    Route::post('shop_update', 'Api\ShopController@shop_update');

    Route::get('/sosaal', function () {
        return Share::load('http://www.example.com', 'Link description')->services('facebook', 'gplus', 'twitter');
    });


});