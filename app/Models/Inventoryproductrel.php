<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Inventoryproductrel extends Model
{
    use HasFactory;
    protected $table = 'jo_inventoryproductrel'; // Adjust if your table name is different
    protected $fillable = ['invoice_id', 'product_id', 'quantity', 'list_price','discount_percent']; // Add other fields as needed
    public $timestamps = false;

    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'id', 'id');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'product_id', 'id');
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
