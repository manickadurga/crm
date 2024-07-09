<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoices extends Model
{
    use HasFactory;
    protected $table = 'jo_invoices';
    protected $primaryKey = 'id';
 
        // protected $fillable = [
        //     'invoicenumber', 'contacts', 'invoicedate', 'duedate', 'discount','discount_suffix',
        //     'currency', 'terms', 'tax1', 'tax2','applydiscount'
        // ];
        protected $guarded = [];
        protected $casts = [
            'tags' => 'array',
            'tax_percent' => 'float'
        ];
        public function products()
        {
            return $this->belongsToMany(Product::class, 'inventoryproductrel')
            ->withPivot('quantity', 'list_price', 'discount_percent');
            return $this->hasMany(InventoryProductRel::class, 'id', 'id');
        }
        public function inventoryProductRels()
        {
            return $this->hasMany(InventoryProductRel::class, 'invoice_id');
        }
}