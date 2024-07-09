<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'jo_products';
    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'options' => 'array',
        'tags' => 'array',
        'add_variants' => 'array'
    ];
    public function invoices()
    {
        return $this->belongsToMany(Invoices::class, 'inventory_product_rel')
                    ->withPivot('quantity', 'list_price', 'discount_percent');
    }
    public function products()
    {
        return $this->hasManyThrough(Product::class, InventoryProductRel::class, 'id', 'product_id', 'id', 'product_id');
    }
}
