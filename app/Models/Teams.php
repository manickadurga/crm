<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teams extends Model
{
    use HasFactory;
    protected $table='jo_teams';
<<<<<<< HEAD
    protected $guarded=[];
    protected $casts=[
        'tags'=>'array'
    ];
    
}
=======
    protected $fillable = [
        'image',
        'team_name',
        'add_or_remove_projects',
        'add_or_remove_managers',
        'add_or_remove_members',
        'tags'
    ];
    protected $casts =[
        'tags'=>'json',
    ];
}
>>>>>>> 68e4740 (Issue -#35)
