<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAction extends Model
{
    use HasFactory;
    protected $table='jo_email_actions';
    protected $guarded=[];
    protected $casts = [
        'attachments' => 'array', // Convert attachments field to array
    ];

    public function trigger()
    {
        return $this->belongsTo(Trigger::class);
    }
}
