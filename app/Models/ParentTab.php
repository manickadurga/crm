<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentTab extends Model
{
    use HasFactory;
    protected $table = 'jo_parenttab';
    protected $primaryKey = 'parenttabid';
    protected $fillable = [
        'parenttab_label', 
        'sequence', 
        'visible'
    ];
}
