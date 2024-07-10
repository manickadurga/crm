<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manage_Employees extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table="jo_manage_employees";
    protected $casts=[
             'tags'=>'array'
         ];
}