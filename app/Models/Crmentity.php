<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crmentity extends Model
{
    use HasFactory;
    protected $table = 'jo_crmentity'; // Specify your table name if different from the model name convention

    protected $primaryKey = 'crmid'; // Specify your primary key if different from 'id'
    
    public $timestamps = false;

    protected $fillable = [
        'crmid',
        'smcreatorid',
        'smownerid',
        'setype',
        'description',
        'createdtime',
        'modifiedtime',
        'viewedtime',
        'status',
        'version',
        'presence',
        'deleted',
        'smgroupid',
        'source',
        'label',
    ];

}
