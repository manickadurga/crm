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
<<<<<<< HEAD
        'location'=>'json',
        'projects'=>'array'
    ];
}
=======
        'location'=>'array',
        'projects'=>'array'
    ];
}
>>>>>>> 68e4740 (Issue -#35)
