<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchants extends Model
{
    use HasFactory;

    protected $table = 'jo_merchants';

    protected $guarded = []; 
    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
        'location' => 'array',
    ];
}
