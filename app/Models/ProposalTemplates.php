<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalTemplates extends Model
{
    use HasFactory;
    protected $table='jo_proposal_templates';
    protected $guarded=[];
}
