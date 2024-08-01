<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    use HasFactory;

    // Specify the table name if it doesn't follow Laravel's naming convention
    protected $table = 'jo_departments';

    // Define the fillable properties for mass assignment
    protected $fillable = [
        'id',
        'departments',
        'add_or_remove_employees',
        'tags',
        'orgid',
    ];

    // Cast the 'tags' attribute to an array
    protected $casts = [
        'tags' => 'array',
        'add_or_remove_employees'=>'array',
    ];
}
