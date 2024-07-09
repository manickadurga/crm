<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InviteLeads extends Model
{
    use HasFactory;
    protected $table = 'jo_invite_leads_';
    protected $guarded = [];
}
