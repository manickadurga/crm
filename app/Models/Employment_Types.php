<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employment_Types extends Model
{
    use HasFactory;
   
    protected $guarded = [];
    protected $table="jo_employment_types";
    protected $casts=[
             'tags'=>'array'
         ];
}