<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportType extends Model
{
    use HasFactory;
    protected $table = 'jo_reporttype';

    protected $fillable = [
        'reportid',
        'data',
    ];
}
