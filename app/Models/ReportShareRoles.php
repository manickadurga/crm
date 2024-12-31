<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportShareRoles extends Model
{
    use HasFactory;
    protected $table='jo_report_shareroles';
    protected $guarded=[];
    protected $primaryKey = 'reportid';
}
