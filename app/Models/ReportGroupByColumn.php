<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportGroupByColumn extends Model
{
    use HasFactory;
    protected $table = 'jo_reportgroupbycolumn';

    protected $fillable = [
        'reportid',
        'sortid',
        'sortcolumn',
        'dategroupbycriteria',
    ];
}
