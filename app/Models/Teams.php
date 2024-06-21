<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teams extends Model
{
    use HasFactory;
    protected $table='jo_teams';
    protected $guarded=[];
    protected $casts=[
        'tags'=>'array'
    ];
    
}
