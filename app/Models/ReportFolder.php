<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportFolder extends Model
{
    use HasFactory;
    protected $table = 'jo_reportfolder';

    protected $primaryKey = 'folderid';
    protected $fillable = [
        'folderid',
        'foldername',
        'description',
        'state'
    ];
}
