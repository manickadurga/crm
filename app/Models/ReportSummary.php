<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportSummary extends Model
{
    use HasFactory;


    protected $table = 'jo_reportsummary';
    protected $primaryKey = 'reportsummaryid';
    protected $fillable = [
        'reportsummaryid',
        'summarytype',
        'columnname',
    ];
}
