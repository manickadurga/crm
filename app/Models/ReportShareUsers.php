<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportShareUsers extends Model
{
    use HasFactory;
    protected $table = 'jo_report_shareusers';

    protected $fillable = [
        'reportid',
        'userid',
    ];
    protected $primaryKey = 'reportid';
}
