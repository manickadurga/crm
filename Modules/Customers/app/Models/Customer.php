<?php

namespace Modules\Customers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Customers\Database\Factories\CustomerFactory;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

     protected $table = 'customers';
    protected $guarded = [];

    protected static function newFactory()
    {
        //return CustomerFactory::new();
    }
}
