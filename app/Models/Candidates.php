<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidates extends Model
{
    use HasFactory;
    protected $table='jo_candidates';
    protected $guarded=[];
    protected $casts=[
        'tags'=>'array',
    ];
}
