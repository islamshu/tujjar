<?php

namespace App\Models\V3;

use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    protected $fillable = ['product_id','name', 'lang'];

    public function product(){
      return $this->belongsTo(Product::class);
    }
}
