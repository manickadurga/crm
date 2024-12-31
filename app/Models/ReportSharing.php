<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSharing extends Model
{
    use HasFactory;
    protected $table = 'jo_reportsharing';

    protected $fillable = [
        'reportid',
        'shareid',
        'setype',
    ];
}
