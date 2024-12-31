<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportShareGroups extends Model
{
    use HasFactory;

    protected $table = 'jo_report_sharegroups';

    protected $fillable = [
        'reportid',
        'groupid',
    ];
    protected $primaryKey = 'reportid';
}
