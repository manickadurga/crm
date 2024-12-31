<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduledReports extends Model
{
    use HasFactory;
    protected $table = 'jo_scheduled_reports';
    protected $fillable = [
        'reportid',
        'recipients',
        'schedule',
        'format',
        'next_trigger_time',
    ];

    protected $casts = [
        'recipients' => 'array',
        'schedule' => 'array',
        'next_trigger_time' => 'datetime',
    ];

}
