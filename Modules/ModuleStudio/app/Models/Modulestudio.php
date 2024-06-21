<?php

namespace Modules\ModuleStudio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ModuleStudio\Database\Factories\ModulestudioFactory;

class Modulestudio extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    // protected $fillable = [];
    protected $table = 'modulestudios'; // Ensure this matches your table name

    protected $fillable = [
        'module_name',
        'version',
        'singular_translation',
        'plural_translation',
        'menu'
    ];

    protected $casts = [
        'fields' => 'array', // Automatically cast the JSON field to an array
    ];

    protected static function newFactory(): ModulestudioFactory
    {
        //return ModulestudioFactory::new();
    }
}
