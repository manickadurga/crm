<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    use HasFactory;
    protected $table='jo_payments';
    protected $guarded=[];
    protected $casts=[
        'tags'=>'array',
    ];

<<<<<<< HEAD
}
=======
}
>>>>>>> 68e4740 (Issue -#35)
