<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InviteCandidates extends Model
{
    use HasFactory;
    protected $table = 'jo_invite_candidates';
    protected $guarded = [];
}
