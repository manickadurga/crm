<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeReportChart extends Model
{
    use HasFactory;

    protected $table = 'jo_homereportchart';

    protected $fillable = [
        'stuffid',
        'reportid',
        'reportcharttype',
    ];
}
