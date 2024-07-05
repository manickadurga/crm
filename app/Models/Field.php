<?php

namespace App\Models;

<<<<<<< HEAD
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
    public function block()
    {
        return $this->belongsTo(Block::class, 'block', 'blockid');
    }

   
}
=======
use Illuminate\Database\Eloquent\Model;

class Field extends Model
    {
        protected $table = 'jo_fields';
        protected $primaryKey = 'fieldid';
        public $timestamps = false; // Assuming timestamps are not used in your existing table
        protected $fillable = ['tabid', 'columnname', 'fieldname', 'fieldlabel']; // Fillable fields

    
     
        public function block()
        {
            return $this->belongsTo(Block::class, 'blockid', 'blockid');
        }
    
        public function module()
        {
            return $this->belongsTo(Module::class, 'tabid', 'tabid');
        }
    }
>>>>>>> 68e4740 (Issue -#35)
