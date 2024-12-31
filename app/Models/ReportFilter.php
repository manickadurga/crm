<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportFilter extends Model
{
    use HasFactory;
    protected $table = 'jo_reportfilter';

    protected $fillable = [
        'filterid',
        'name',
    ];
}
