<?php

namespace Modules\Customers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Customers\Database\Factories\CustomersFactory;

class Customers extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table='customers';
    protected $guarded=[];
    protected $casts=[
        'projects'=>'array',
        'tags'=>'array',
        'dynamic_fields'=>'array'

    ];

    protected static function newFactory()
    {
        //return CustomersFactory::new();
    }
}
