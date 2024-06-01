<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParentTabRel extends Model
{
    use HasFactory;
    protected $table = "jo_parenttabrel";
    protected $fillable = [
        'parenttabid', 
        'tabid', 
        'sequence'
    ];
}
