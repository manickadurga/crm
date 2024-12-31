<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $table = 'jo_templates';
    protected $fillable = [
        'template_name',
        'body',
    ];

    // Ensure body is cast to an array
    protected $casts = [
        'body' => 'array',
    ];
}
