<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamTask extends Model
{
    use HasFactory;
    protected $table='jo_teamtasks';
    protected $fillable = [
        'tasknumber', 'projects', 'status', 'teams', 'title', 
        'priority', 'size', 'tags', 'duedate', 'estimate','description','estimate_days', 
        'estimate_hours', 
        'estimate_minutes',
    ];

    protected $casts = [
      
        'estimate' =>'array', 
        
        'teams'=>'array',
        'tags'=>'array'
    ];}


