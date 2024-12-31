<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MailScannerFolders extends Model
{
    use HasFactory;
    protected $table = 'jo_mailscanner_folders';

    protected $fillable = [
        'folderid',
        'scannerid',
        'foldername',
        'lastscan',
        'rescan',
        'renabled',
    ];
}
