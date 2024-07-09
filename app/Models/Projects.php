<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    use HasFactory;
    protected $table = 'jo_projects'; // Correct table name
    
    // Other model properties and methods
<<<<<<< HEAD
    protected $guarded=[];
    
}
=======
    protected $fillable = [
        'image',
        'name',
        'code',
        'project_url',
        'owner',
        'clients',
        'add_or_remove_employees',
        'add_or_remove_teams',
        'project_start_date',
        'project_end_date',
        'description',
        'tags',
        'billing',
        'currency',
        'type',
        'cost',
        'open_source',
        'color',
        'task_view_mode',
        'public',
        'billable'
    ];
    protected $casts = [
        'tags' => 'array',
    ]; 
    
}

>>>>>>> 68e4740 (Issue -#35)
