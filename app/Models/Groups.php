<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groups extends Model
{
    use HasFactory;
    protected $table='jo_groups';
    protected $guarded=[];
    protected $casts=[
        'group_members'=>'array',
    ];

}
