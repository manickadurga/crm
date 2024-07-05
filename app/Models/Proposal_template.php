<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal_template extends Model
{
    protected $table = 'jo_proposal_template'; // Correct table name
    
    // Other model properties and methods
    protected $fillable = [
        'select_employee', 
        'name', 
        'content', 
        'org_id'
    ];
}