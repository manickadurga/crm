<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tasks extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = [];
    protected $table="jo_tasks";
        protected $casts=[
            'tags'=>'array',
            'estimate'=>'array'
            
        ];
    protected static function newFactory()
    {
        //return TasksFactory::new();
    }
}

