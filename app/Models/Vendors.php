<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendors extends Model
{
    use HasFactory;
<<<<<<< HEAD
    protected $table='jo_vendors';
    protected $guarded=[];
    protected $casts=[

        'tags'=>'array',
    ];
}
=======
    protected $guarded = [];
    protected $table="jo_vendors";
    protected $casts=[
             'tags'=>'array'
         ];
        
}

>>>>>>> 68e4740 (Issue -#35)
