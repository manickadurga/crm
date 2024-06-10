<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomersInvite extends Model
{
    use HasFactory;
    protected $table='jo_customers_invite';
    protected $guarded=[];
}
