<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TriggerFilter extends Model
{
    use HasFactory;
    protected $table='jo_trigger_filters';
    protected $guarded=[];
    public function trigger()
    {
        return $this->belongsTo(Trigger::class);
    }
}
