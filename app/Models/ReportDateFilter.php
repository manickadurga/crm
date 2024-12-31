<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportDateFilter extends Model
{
    use HasFactory;

    protected $table = 'jo_reportdatefilter';

    protected $fillable = [
        'datefilderid',
        'datecolumnname',
        'datefilder',
        'startdate',
        'enddate',
    ];

    protected $dates = [
        'startdate',
        'enddate',
    ];
}
