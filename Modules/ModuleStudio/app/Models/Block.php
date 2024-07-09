<?php

namespace Modules\ModuleStudio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ModuleStudio\Database\Factories\BlockFactory;

class Block extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'blocks';
    protected $primaryKey = 'blockid';
    
    protected $fillable = [
       'block_label',
       'sequence',
       'show_title',
        'visible',
        'create_view',
        'edit_view',
        'detail_view',
        'display_status',
        'iscustom'

    ];

    protected $casts = [
        'show_title' => 'boolean',
        'visible' => 'boolean',
        'create_view' => 'boolean',
        'edit_view' => 'boolean',
        'detail_view' => 'boolean',
        'display_status' => 'boolean',
        'iscustom' => 'boolean',
    ];


    protected static function newFactory(): BlockFactory
    {
        //return BlockFactory::new();
    }
}
