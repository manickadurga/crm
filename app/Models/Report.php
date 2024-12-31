<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $table="jo_reports";

    protected $fillable = [
        'reportid',
        'folderid',
        'reportname',
        'description',
        'reporttype',
        'queryid',
        'state',
        'customizable',
        'category',
        'owner',
        'sharingtype',
    ];
    protected $primaryKey = 'reportid'; // Adjust if using a different primary key
    public $incrementing = true;
    protected $keyType = 'int'; 
}
