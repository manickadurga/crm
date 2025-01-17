<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTypes extends Model
{
    use HasFactory;
    protected $table = 'jo_product_types';
    protected $guarded=[];

public function products()
{
    return $this->hasMany(Product::class);
}
}
