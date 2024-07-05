<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposals extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table="jo_proposals";
    protected $casts=[
             'tags'=>'array'
         ];
        


 }
 