<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Customers;


class Opportunity extends Model
{
    use HasFactory;
    protected $table='jo_opportunities';
    protected $guarded=[];
    protected $casts=[
        'additional_contacts'=>'array',
        'followers'=>'array',
        'tags'=>'array',
    ];

    public function contact()
{
    return $this->belongsTo(Customers::class, 'contact_id');
}
}
