<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'jo_positions';

    protected $fillable = ['position_name', 'tags'];

    // Cast 'tags' as an array since it's stored as JSON
    protected $casts = [
        'tags' => 'array',
    ];
}
