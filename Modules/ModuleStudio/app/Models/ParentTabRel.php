<?php

namespace Modules\ModuleStudio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ModuleStudio\Database\Factories\ParentTabRelFactory;

class ParentTabRel extends Model
{
    use HasFactory;
   protected $table = "parenttabrel";
   protected $fillable = [
        'parenttabid', 
        'tabid', 
        'sequence'
    ];

    protected static function newFactory(): ParentTabRelFactory
    {
        //return ParentTabRelFactory::new();
    }
}
