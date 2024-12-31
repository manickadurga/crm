<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsAction extends Model
{
    use HasFactory;
    protected $table='jo_sms_actions';
    protected $guarded=[];
    protected $casts = [
        'attachments' => 'array',
    ];
    public function trigger()
    {
        return $this->belongsTo(Trigger::class);
    }

}
