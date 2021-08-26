<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductDetailCollection;
use App\Http\Resources\SearchProductCollection;
use App\Http\Resources\FlashDealCollection;
use App\Models\FlashDeal;
use App\Models\FlashDealProduct;
use App\Models\Product;
use App\Models\Color;
use App\Upload;
use App\ProductStock;
use Illuminate\Http\Request;
use App\Utility\CategoryUtility;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\BaseController as BaseController;

class ProductController extends BaseController
{
    public function index()
    {
        $products = new ProductCollection(Product::whereIn('user_id', verified_sellers_id())->latest()->paginate(10));
        return $this->sendResponse($products, translate('products'));
    }

    public function show($id)
    {
        $show = new ProductDetailCollection(Product::whereIn('user_id', verified_sellers_id())->where('id', $id)->get());
        return $this->sendResponse($show, translate('this is product'));
    }

    public function admin()
    {
        $admin = new ProductCollection(Product::whereIn('user_id', verified_sellers_id())->where('added_by', 'admin')->latest()->paginate(10));
        return $this->sendResponse($admin, translate('admin products'));
    }

    public function seller()
    {
        $seller = new ProductCollection(Product::whereIn('user_id', verified_sellers_id())->where('added_by', 'seller')->latest()->paginate(10));
        return $this->sendResponse($seller, translate('seller products'));
    }

    public function category($id)
    {
        $category_ids = CategoryUtility::children_ids($id);
        $category_ids[] = $id;
        $cat = new ProductCollection(Product::whereIn('user_id', verified_sellers_id())->whereIn('category_id', $category_ids)->latest()->get());
        return $this->sendResponse($cat, translate('categories products'));
    }

    public function subCategory($id)
    {
        $category_ids = CategoryUtility::children_ids($id);
        $category_ids[] = $id;
        $cat = new ProductCollection(Product::whereIn('user_id', verified_sellers_id())->whereIn('category_id', $category_ids)->latest()->paginate(10));
        return $this->sendResponse($cat, translate('subcategories products'));
    }

    public function subSubCategory($id)
    {
        $category_ids = CategoryUtility::children_ids($id);
        $category_ids[] = $id;
        $cat = new ProductCollection(Product::whereIn('user_id', verified_sellers_id())->whereIn('category_id', $category_ids)->latest()->paginate(10));
        return $this->sendResponse($cat, translate('subSubCategory products'));
    }

    public function brand($id)
    {
        $brand = new ProductCollection(Product::whereIn('user_id', verified_sellers_id())->where('brand_id', $id)->latest()->paginate(10));
        return $this->sendResponse($brand, translate('brand products'));
    }

    public function todaysDeal()
    {
        $todays = new ProductCollection(Product::whereIn('user_id', verified_sellers_id())->where('todays_deal', 1)->latest()->get());
        return $this->sendResponse($todays, translate('today deals'));
    }

    public function flashDeal()
    {
        $flashes = FlashDeal::where('status', 1)->where('featured', 1)->where('start_date', '<=', strtotime(date('d-m-Y')))->where('end_date', '>=', strtotime(date('d-m-Y')))->get();
        if ($flashes->count() > 0)
            $flashes = new FlashDealCollection($flashes);
        return $this->sendResponse($flashes, translate('flash deals'));
    }

    public function featured()
    {
        $fetured = new ProductCollection(Product::whereIn('user_id', verified_sellers_id())->where('featured', 1)->latest()->get());
        return $this->sendResponse($fetured, translate('featured'));
    }

    public function bestSeller()
    {
        $best = new ProductCollection(Product::orderBy('num_of_sale', 'desc')->limit(20)->get());
        return $this->sendResponse($best, translate('best seller'));
    }

    public function related($id)
    {
        $product = Product::find($id);
        $related = new ProductCollection(Product::whereIn('user_id', verified_sellers_id())->where('category_id', $product->category_id)->where('id', '!=', $id)->limit(10)->get());
        return $this->sendResponse($related, translate('related product'));
    }

    public function topFromSeller($id)
    {
        $product = Product::find($id);
        $related = new ProductCollection(Product::whereIn('user_id', verified_sellers_id())->where('user_id', $product->user_id)->orderBy('num_of_sale', 'desc')->limit(4)->get());
        return $this->sendResponse($related, translate('topFromSeller product'));
    }

    public function search(Request $request)
    {
        $key = $request->key;
        $scope = $request->scope;
        switch ($scope) {
            case 'price_low_to_high':
                $collection = new SearchProductCollection(Product::whereIn('user_id', verified_sellers_id())->where('name', 'like', "%{$key}%")->orWhere('name_ar', 'like', "%{$key}%")->orWhere('tags', 'like', "%{$key}%")->orderBy('unit_price', 'asc')->paginate(10));
                $collection->appends(['key' => $key, 'scope' => $scope]);
                return $this->sendResponse($collection, translate('price_low_to_high'));
            case 'price_high_to_low':
                $collection = new SearchProductCollection(Product::whereIn('user_id', verified_sellers_id())->where('name', 'like', "%{$key}%")->orWhere('name_ar', 'like', "%{$key}%")->orWhere('tags', 'like', "%{$key}%")->orderBy('unit_price', 'desc')->paginate(10));
                $collection->appends(['key' => $key, 'scope' => $scope]);
                return $this->sendResponse($collection, translate('price_high_to_low'));
            case 'new_arrival':
                $collection = new SearchProductCollection(Product::whereIn('user_id', verified_sellers_id())->where('name', 'like', "%{$key}%")->orWhere('name_ar', 'like', "%{$key}%")->orWhere('tags', 'like', "%{$key}%")->orderBy('created_at', 'desc')->paginate(10));
                $collection->appends(['key' => $key, 'scope' => $scope]);
                return $this->sendResponse($collection, translate('new_arrival'));
            case 'popularity':
                $collection = new SearchProductCollection(Product::whereIn('user_id', verified_sellers_id())->where('name', 'like', "%{$key}%")->orWhere('name_ar', 'like', "%{$key}%")->orWhere('tags', 'like', "%{$key}%")->orderBy('num_of_sale', 'desc')->paginate(10));
                $collection->appends(['key' => $key, 'scope' => $scope]);
                return $this->sendResponse($collection, translate('popularity'));
            case 'top_rated':
                $collection = new SearchProductCollection(Product::whereIn('user_id', verified_sellers_id())->where('name', 'like', "%{$key}%")->orWhere('name_ar', 'like', "%{$key}%")->orWhere('tags', 'like', "%{$key}%")->orderBy('rating', 'desc')->paginate(10));
                $collection->appends(['key' => $key, 'scope' => $scope]);
                return $this->sendResponse($collection, translate('top_rated'));
            default:
                $collection = new SearchProductCollection(Product::where('name', 'like', "%{$key}%")->orWhere('name_ar', 'like', "%{$key}%")->orWhere('tags', 'like', "%{$key}%")->orderBy('num_of_sale', 'desc')->paginate(10));
                $collection->appends(['key' => $key, 'scope' => $scope]);
                return $this->sendResponse($collection, translate('Search'));
        }
    }

    public function variantPrice(Request $request)
    {
        $product = Product::findOrFail($request->id);
        $str = '';
        if ($request->has('color')) {
            $data['color'] = $request['color'];
            $str = Color::where('code', $request['color'])->first()->name;
        }
        foreach (json_decode($request->choice) as $option) {
            $str .= $str != '' ? '-' . str_replace(' ', '', $option->name) : str_replace(' ', '', $option->name);
        }
        if ($str != null && $product->variant_product) {
            $product_stock = $product->stocks->where('variant', $str)->first();
            $price = $product_stock->price;
            $stockQuantity = $product_stock->qty;
        } else {
            $price = $product->unit_price;
            $stockQuantity = $product->current_stock;
        }
        $flash_deals = FlashDeal::where('status', 1)->get();
        $inFlashDeal = false;
        foreach ($flash_deals as $key => $flash_deal) {
            if ($flash_deal != null && $flash_deal->status == 1 && strtotime(date('d-m-Y')) >= $flash_deal->start_date && strtotime(date('d-m-Y')) <= $flash_deal->end_date && FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first() != null) {
                $flash_deal_product = FlashDealProduct::where('flash_deal_id', $flash_deal->id)->where('product_id', $product->id)->first();
                if ($flash_deal_product->discount_type == 'percent') {
                    $price -= ($price * $flash_deal_product->discount) / 100;
                } elseif ($flash_deal_product->discount_type == 'amount') {
                    $price -= $flash_deal_product->discount;
                }
                $inFlashDeal = true;
                break;
            }
        }
        if (!$inFlashDeal) {
            if ($product->discount_type == 'percent')
                $price -= ($price * $product->discount) / 100;
            elseif ($product->discount_type == 'amount')
                $price -= $product->discount;
        }
        if ($product->tax_type == 'percent')
            $price += ($price * $product->tax) / 100;
        elseif ($product->tax_type == 'amount')
            $price += $product->tax;
        return response()->json([
            'product_id' => $product->id,
            'variant' => $str,
            'price' => (double)$price,
            'in_stock' => $stockQuantity < 1 ? false : true
        ]);
    }

    public function home()
    {
        return new ProductCollection(Product::whereIn('user_id', verified_sellers_id())->inRandomOrder()->take(50)->get());
    }

    public function seller_product($atts, Request $request)
    {
        if ($atts == 'create') {
            $product = new Product;
            $product->name = $request->name_en;
            $product->name_ar = $request->name_ar;
            $product->added_by = 'seller';
            $product->user_id = auth('api')->user()->id;
            $product->category_id = $request->category_id;
            $product->brand_id = $request->brand_id;
            $product->current_stock = $request->current_stock;
            if ($request->hasFile('photos')) {
                foreach ($request->photos as $imdex1 => $img) {
                    $upload = new Upload;
                    $upload->file_original_name = null;
                    $arr = explode('.', $img->getClientOriginalName());
                    for ($i = 0; $i < count($arr) - 1; $i++) {
                        if ($i == 0)
                            $upload->file_original_name .= $arr[$i];
                        else
                            $upload->file_original_name .= "." . $arr[$i];
                    }
                    $upload->file_name = $img->store('uploads/all');
                    $upload->user_id = auth('api')->user()->id;
                    $upload->extension = strtolower($img->getClientOriginalExtension());
                    if (isset($type[$upload->extension]))
                        $upload->type = $type[$upload->extension];
                    else
                        $upload->type = "others";
                    $upload->file_size = $img->getSize();
                    $upload->save();
                    if ($imdex1 == 0)
                        $product->thumbnail_img = $upload->id;
                    $arrr[] = $upload->id;
                }
                $arrt = json_encode($arrr);
                $array = str_replace('[', '', $arrt);
                $array1 = str_replace(']', '', $array);
                $product->photos = $array1;
            }
            $product->unit = $request->unit;
            $product->min_qty = $request->min_qty;
            $product->description = $request->description_en;
            $product->description_ar = $request->description_ar;
            $product->unit_price = $request->unit_price;
            $product->purchase_price = $request->purchase_price;
            $product->tax = get_setting('tax');
            $product->tax_type = get_setting('tax_type');
            $product->discount = $request->discount;
            $product->discount_type = $request->discount_type;
            $product->meta_title = $product->name;
            $product->meta_description = $product->description;
            $product->slug = preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $request->name_en)) . '-' . Str::random(5);
            if ($request->colors_id != null)
                $product->colors = json_encode($request->colors_id);
            else {
                $colorss = array();
                $product->colors = json_encode($colorss);
            }
            $choice_options = array();
            if ($request->size != null || $request->fabric != null) {
                if ($request->has('size')) {
                    $item['attribute_id'] = 1;
                    $data = array();
                    foreach ($request->size as $key => $eachValue) {
                        array_push($data, $eachValue);
                    }
                    $item['values'] = $data;
                    array_push($choice_options, $item);
                }
                if ($request->has('fabric')) {
                    $item['attribute_id'] = 2;
                    $data = array();
                    foreach ($request->fabric as $key => $eachValueq) {
                        array_push($data, $eachValueq);
                    }
                    $item['values'] = $data;
                    array_push($choice_options, $item);
                }
            } else
                $choice_options = array();
            $product->choice_options = json_encode($choice_options);
            $product->save();
            $options = array();
            if ($request->has('colors_id'))
                array_push($options, $request->colors_id);
            if ($request->has('size') || $request->has('fabric')) {
                if ($request->has('size')) {
                    $data = array();
                    foreach ($request->size as $key => $item) {
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
                if ($request->has('fabric')) {
                    $data = array();
                    foreach ($request->fabric as $key => $item) {
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
                $arryee = [];
                if ($request->size != null)
                    array_push($arryee, "1");
                if ($request->fabric != null)
                    array_push($arryee, "2");
                $product->attributes = json_encode($arryee);
            } else
                $product->attributes = array();
            $combinations = combinations($options);
            if (count($combinations[0]) > 0) {
                $product->variant_product = 1;
                foreach ($combinations as $key => $combination) {
                    $str = '';
                    foreach ($combination as $key => $item) {
                        if ($key > 0)
                            $str .= '-' . str_replace(' ', '', $item);
                        else {
                            if ($request->has('colors_id')) {
                                $color_name = \App\Color::where('code', $item)->first()->name;
                                $str .= $color_name;
                            } else {
                                $str .= str_replace(' ', '', $item);
                            }
                        }
                    }
                    $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                    if ($product_stock == null) {
                        $product_stock = new ProductStock;
                        $product_stock->product_id = $product->id;
                    }
                    $product_stock->variant = $str;
                    $product_stock->price = $request->unit_price;
                    $product_stock->sku = $request['sku_' . str_replace('.', '_', $str)];
                    $product_stock->qty = $request->current_stock;
                    $product_stock->save();
                }
            } else {
                $product_stock = new ProductStock;
                $product_stock->product_id = $product->id;
                $product_stock->price = $request->unit_price;
                $product_stock->qty = $request->current_stock;
                $product_stock->save();
            }
            if ($product->save())
                return $this->sendResponse($product, translate('products created Successfully.'));
            else
                return $this->sendError(translate('error occer'));
        } elseif ($atts == 'update') {
            $product = Product::find($request->product_id);
            if (!$product)
                return $this->sendError(translate('no Product'));
            $product->name = $request->name_en;
            $product->name_ar = $request->name_ar;
            $product->added_by = 'seller';
            $product->user_id = auth('api')->user()->id;
            $product->category_id = $request->category_id;
            $product->brand_id = $request->brand_id;
            $product->current_stock = $request->current_stock;
            if ($request->hasFile('photos')) {
                foreach ($request->photos as $imdex1 => $img) {
                    $upload = new Upload;
                    $upload->file_original_name = null;
                    $arr = explode('.', $img->getClientOriginalName());
                    for ($i = 0; $i < count($arr) - 1; $i++) {
                        if ($i == 0)
                            $upload->file_original_name .= $arr[$i];
                        else
                            $upload->file_original_name .= "." . $arr[$i];
                    }
                    $upload->file_name = $img->store('uploads/all');
                    $upload->user_id = auth('api')->user()->id;
                    $upload->extension = strtolower($img->getClientOriginalExtension());
                    if (isset($type[$upload->extension]))
                        $upload->type = $type[$upload->extension];
                    else
                        $upload->type = "others";
                    $upload->file_size = $img->getSize();

                    $upload->save();
                    if ($imdex1 == 0)
                        $product->thumbnail_img = $upload->id;
                    $arrr[] = $upload->id;
                }
                $arrt = json_encode($arrr);
                $array = str_replace('[', '', $arrt);
                $array1 = str_replace(']', '', $array);
                $product->photos = $array1;
            }
            $product->unit = $request->unit;
            $product->min_qty = $request->min_qty;
            $product->description = $request->description_en;
            $product->description_ar = $request->description_ar;
            $product->unit_price = $request->unit_price;
            $product->purchase_price = $request->purchase_price;
            $product->tax = get_setting('tax');
            $product->tax_type = get_setting('tax_type');
            $product->discount = $request->discount;
            $product->discount_type = $request->discount_type;
            $product->meta_title = $product->name;
            $product->meta_description = $product->description;
            if ($request->colors_id != null)
                $product->colors = json_encode($request->colors_id);
            else {
                $colorss = array();
                $product->colors = json_encode($colorss);
            }
            $choice_options = array();
            if ($request->size != null || $request->fabric != null) {
                if ($request->has('size')) {
                    $item['attribute_id'] = 1;
                    $data = array();
                    foreach ($request->size as $key => $eachValue) {
                        array_push($data, $eachValue);
                    }
                    $item['values'] = $data;
                    array_push($choice_options, $item);
                }
                if ($request->has('fabric')) {
                    $item['attribute_id'] = 2;
                    $data = array();
                    foreach ($request->fabric as $key => $eachValueq) {
                        array_push($data, $eachValueq);
                    }
                    $item['values'] = $data;
                    array_push($choice_options, $item);
                }
            } else
                $choice_options = array();
            $product->choice_options = json_encode($choice_options);
            $product->save();
            $options = array();
            if ($request->has('colors_id'))
                array_push($options, $request->colors_id);
            if ($request->has('size') || $request->has('fabric')) {
                if ($request->has('size')) {
                    $data = array();
                    foreach ($request->size as $key => $item) {
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
                if ($request->has('fabric')) {
                    $data = array();
                    foreach ($request->fabric as $key => $item) {
                        array_push($data, $item);
                    }
                    array_push($options, $data);
                }
                $arryee = [];
                if ($request->size != null)
                    array_push($arryee, "1");
                if ($request->fabric != null)
                    array_push($arryee, "2");
                $product->attributes = json_encode($arryee);
            } else
                $product->attributes = array();
            $combinations = combinations($options);
            if (count($combinations[0]) > 0) {
                $product->variant_product = 1;
                foreach ($combinations as $key => $combination) {
                    $str = '';
                    foreach ($combination as $key => $item) {
                        if ($key > 0)
                            $str .= '-' . str_replace(' ', '', $item);
                        else {
                            if ($request->has('colors_id')) {
                                $color_name = \App\Color::where('code', $item)->first()->name;
                                $str .= $color_name;
                            } else
                                $str .= str_replace(' ', '', $item);
                        }
                    }
                    $product_stock = ProductStock::where('product_id', $product->id)->where('variant', $str)->first();
                    if ($product_stock == null) {
                        $product_stock = new ProductStock;
                        $product_stock->product_id = $product->id;
                    }
                    $product_stock->variant = $str;
                    $product_stock->price = $request->unit_price;
                    $product_stock->sku = $request['sku_' . str_replace('.', '_', $str)];
                    $product_stock->qty = $request->current_stock;
                    $product_stock->save();
                }
            } else {
                $product_stock = new ProductStock;
                $product_stock->product_id = $product->id;
                $product_stock->price = $request->unit_price;
                $product_stock->qty = $request->current_stock;
                $product_stock->save();
            }
            if ($product->save())
                return $this->sendResponse($product, translate('products created Successfully.'));
            else
                return $this->sendError(translate('error occer'));
        }
    }
}
