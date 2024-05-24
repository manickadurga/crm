<?php

namespace Modules\ModuleStudio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ModuleStudio\Database\Factories\ParentTabFactory;

class ParentTab extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'parenttab';
    protected $primaryKey = 'parenttabid';
    protected $fillable = [
        'parenttab_label', 
        'sequence', 
        'visible'
    ];

    protected static function newFactory(): ParentTabFactory
    {
        //return ParentTabFactory::new();
    }
}
