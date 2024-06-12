<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'jo_products';
    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
        'options' => 'array',
        'tags' => 'array',
        'add_variants' => 'array'
    ];

}
