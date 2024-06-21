<?php

namespace Modules\ModuleStudio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ModuleStudio\Database\Factories\TabFactory;

class Tab extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'tabs';
    protected $primaryKey = 'tabid';
    protected $guarded = [];


    protected static function newFactory(): TabFactory
    {
        //return TabFactory::new();
    }
}