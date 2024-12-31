<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeLevel extends Model
{
    use HasFactory;
    protected $table='jo_employee_levels';
    protected $guarded=[];
    protected $casts=[
        'tags'=>'array'
    ];
}
