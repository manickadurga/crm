<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSortCol extends Model
{
    use HasFactory;
    protected $table = 'jo_reportsortcol';
    protected $primaryKey = 'sortcolid';

    protected $fillable = [
        'sortcolid',
        'reportid',
        'columnname',
        'sortorder',
    ];
}
