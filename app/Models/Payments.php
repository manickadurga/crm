<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory;
    protected $table='jo_payments';
    protected $guarded=[];
    protected $casts=[
        'tags'=>'array',
    ];
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'contacts');
    }
    public function project()
    {
        return $this->belongsTo(Project::class, 'projects');
    }
    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'invoice_number', 'id');
    }
    public function contact()
{
    return $this->belongsTo(Customers::class, 'contacts');
}




}
