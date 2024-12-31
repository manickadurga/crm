<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelCriteria extends Model
{
    use HasFactory;

    protected $table = 'jo_relcriteria';

    protected $fillable = [
        'queryid',
        'columnindex',
        'columnname',
        'comparator',
        'value',
        'groupid',
        'column_condition',
    ];
    protected $primaryKey = 'queryid'; // Specify the primary key
    public $incrementing = false; // If it's not auto-incrementing
    protected $keyType = 'bigint';
}
