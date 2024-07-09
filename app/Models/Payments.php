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

}
