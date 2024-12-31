<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tab;
use App\Models\Blocks;

class FormField extends Model
{
    use HasFactory;

    protected $table = 'formfields'; // Table name
    protected $primaryKey = 'fieldid'; // Primary key for the table

    // Define fillable fields
    protected $fillable = [
        'tabid',
        'block',
        'trigger_or_action',
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
        'displaytype',
        'typeofdata',
        'quickcreate',
        'quickcreatesequence',
        'info_type',
        'masseditable',
        'helpinfo',
        'summaryfield',
        'headerfield',
        'orgid',
    ];

    // Optionally, define relationships if needed (e.g., belongsTo a Tab or Block)
    public function tab()
    {
        return $this->belongsTo(Tab::class, 'tabid', 'tabid');
    }

    

    public function block()
    {
        return $this->belongsTo(Blocks::class, 'block', 'blockid');
    }
}
