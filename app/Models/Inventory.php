<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'jo_products';

    //protected $table = 'jo_inventories';

    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'options' => 'array',
        'tags' => 'array',
        'add_variants' => 'array'
    ];

    public function productType()
    {
        return $this->belongsTo(ProductTypes::class, 'product_type', 'name');
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategories::class, 'product_category', 'name');
    }
}
