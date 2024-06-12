<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentsSharing extends Model
{
    use HasFactory;
    protected $table='jo_equipments_sharing';
    protected $guarded=[];
    protected $casts=[
       'add_or_remove_employees'=>'array',

    ];
}
