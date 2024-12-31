<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trigger extends Model
{
    use HasFactory;
    protected $table='triggers';

    protected $fillable = ['trigger_name', 'filters']; // Allow filters to be mass assignable

    // Optionally, you can cast filters to an array
    protected $casts = [
        'filters' => 'array', // Automatically handle filters as an array
    ];
}
