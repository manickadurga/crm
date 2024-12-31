<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportModules extends Model
{
    use HasFactory;

    // Specify the table associated with the model
    protected $table = 'jo_reportmodules';

    // Define the attributes that are mass assignable
    protected $fillable = [
        'reportmodulesid',
        'primarymodule',
        'secondarymodules',
    ];

    // Specify the primary key for the model
    protected $primaryKey = 'reportmodulesid';

    // Indicates if the IDs are auto-incrementing. Set to false if not
    public $incrementing = false;

    // Specify the type of the primary key
    protected $keyType = 'bigint';
}
