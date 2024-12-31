<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleReports extends Model
{
    use HasFactory;

    use HasFactory;
    protected $table = 'jo_schedulereports';
    protected $fillable = [
        'reportid',
        'scheduleid',
        'recipients',
        'schdate',
        'schtime',
        'schdayoftheweek',
        'schdayofthemonth',
        'schannualdates',
        'specificemails',
        'next_trigger_time',
        'fileformat',
    ];
    protected $primaryKey = 'reportid'; // Ensure this matches your database schema
    public $incrementing = true;
    protected $keyType = 'int';
}
