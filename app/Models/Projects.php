<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    use HasFactory;
    protected $table = 'jo_projects'; // Correct table name
    
    // Other model properties and methods

    protected $guarded=[];
    
}


