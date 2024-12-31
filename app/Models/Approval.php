<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $table = 'jo_approvals';
    protected $guarded=[];
    protected $casts=[
        'tags'=>'array',
        'choose_employees'=>'array',
        'choose_teams'=>'array',
    ];


}
