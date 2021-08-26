<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;
use App;
use Str;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'added_by', 'user_id', 'category_id', 'brand_id', 'video_provider', 'video_link', 'unit_price',
        'purchase_price', 'unit', 'slug', 'colors', 'choice_options', 'variations', 'current_stock'];

    public function getTranslation($field = '', $lang = false)
    {
        $lang = $lang == false ? App::getLocale() : $lang;
        $product_translations = $this->hasMany(ProductTranslation::class)->where('lang', $lang)->first();
        return $product_translations != null ? $product_translations->$field : $this->$field;
    }

    public function product_translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('status', 1);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function stocks()
    {
        return $this->hasMany(ProductStock::class);
    }

    public function langname()
    {
        $dir = Language::where('code', Session()->get('locale'))->first()->rtl;
        if ($dir == 1)
            return Str::limit($this->name_ar, 13);
        else
            return Str::limit($this->name, 10);
    }

    public function langdes()
    {
        $dir = Language::where('code', Session()->get('locale'))->first()->rtl;
        if ($dir == 1)
            return $this->description_ar;
        else
            return $this->description;
    }

    public function all_langname()
    {
        $dir = Language::where('code', Session()->get('locale'))->first()->rtl;
        if ($dir == 1)
            return $this->name_ar;
        else
            return $this->name;
    }
}
