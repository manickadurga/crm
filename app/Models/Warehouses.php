<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouses extends Model
{
    use HasFactory;
    
    protected $table = 'jo_warehouses';
    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
        'location' => 'array',
        'active' => 'boolean',
    ];
}
