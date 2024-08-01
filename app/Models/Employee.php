<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table='jo_manage_employees';
    protected $guarded=[];
    protected $casts=[
        'tags'=>'array',
    ];

}
