<?php

namespace Modules\ModuleStudio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ModuleStudio\Database\Factories\RelatedListFactory;

class RelatedList extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'relatedlists';
    protected $primaryKey = 'relationid';
    protected $fillable = [
        'tabid',
        'related_tabid',
        'name',
        'sequence',
        'label',
        'presence',
        'actions',
        'relationfieldid',
        'source',
        'relationtype',
    ];
    public function tab()
    {
        return $this->belongsTo(Tab::class, 'tabid');
    }

    public function relatedTab()
    {
        return $this->belongsTo(Tab::class, 'related_tabid');
    }
    protected static function newFactory(): RelatedListFactory
    {
        //return RelatedListFactory::new();
    }
}
