<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customers;

class Estimate extends Model
{
    use HasFactory;

    protected $table = 'jo_estimates';
    //protected $primaryKey = 'id';
    protected $fillable = [
        'estimatenumber', 
        'contacts',
        'estimatedate',
        'duedate',
        'discount',
        'discount_suffix',
        'currency',
        'terms',
        'tags',
        'tax1',
        'tax1_suffix',
        'tax2',
        'tax2_suffix',
        'applydiscount',
        'taxtype',
        'subtotal',
        'total',
        'tax_percent',
        'discount_percent',
        'tax_amount',
        'estimate_status',
        'organization_name',
    ];
    protected $guarded = [];

    public function tasks()
    {
        // Assuming there's a Task model related to Estimate
        return $this->hasMany(Tasks::class);
    }

    public function contact()
{
    return $this->belongsTo(Customers::class, 'contacts');
}


}