<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pipelines extends Model
{
    use HasFactory;
    protected $table='jo_pipelines';
    protected $guarded=[];
    protected $casts=[
        'stages'=>'json',

    ];
<<<<<<< HEAD
}
=======
}
>>>>>>> 68e4740 (Issue -#35)
