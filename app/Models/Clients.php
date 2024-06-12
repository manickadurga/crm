<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    use HasFactory;
    protected $table='jo_clients';
    protected $guarded=[];
    protected $casts=[
        'projects'=>'array',
        'tags'=>'array',
        'location'=>'json',

    ];

}
