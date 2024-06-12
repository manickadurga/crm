<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leads extends Model
{
    use HasFactory;
    protected $table = 'jo_leads';
    protected $guarded = [];
    protected $casts = [
        'tags' => 'array',
        'location'=>'json',
        'projects'=>'array'
    ];
}
