<?php

namespace App\Models;
use App\Models\Tab;
use App\Models\Block;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use HasFactory;
    protected $table = 'jo_fields';
    protected $primaryKey = 'fieldid';
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

    

    public function block()
    {
        return $this->belongsTo(Block::class, 'block', 'blockid');
    }

}


   
