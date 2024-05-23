<?php

namespace Modules\ModuleStudio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ModuleStudio\Database\Factories\FieldFactory;

class Field extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'fields';

    // Define the primary key if it's not 'id'
    protected $primaryKey = 'fieldid';

    // Specify the columns that are mass assignable
    protected $fillable = [
        'tabid',
        'columnname',
        'tablename',
        'generatedtype',
        'uitype',
        'fieldname',
        'fieldlabel',
        'readonly',
        'presence',
        'defaultvalue',
        'maximumlength',
        'sequence',
        'block',
        'displaytype',
        'typeofdata',
        'quickcreate',
        'quickcreatesequence',
        'info_type',
        'masseditable',
        'helpinfo',
        'summaryfield',
        'headerfield',
    ];

    public function tab()
    {
        return $this->belongsTo(Tab::class, 'tabid', 'tabid');
    }

    // Define the relationship to the Block model
    public function block()
    {
        return $this->belongsTo(Block::class, 'block', 'blockid');
    }

    protected static function newFactory(): FieldFactory
    {
        //return FieldFactory::new();
    }
}
