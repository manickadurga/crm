<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile2Field extends Model
{
    use HasFactory;
    protected $table='jo_profile2field';
    protected $guarded=[];
    protected $casts = [
        'visible' => 'boolean',
        'readonly' => 'boolean',
    ];
}
