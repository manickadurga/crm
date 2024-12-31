<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationTrigger extends Model
{
    use HasFactory;
    protected $table='jo_automation_triggers';
    protected $guarded=[];
    protected $casts=[
        'message_details'=>'json'
    ];
}
