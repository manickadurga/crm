<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelCriteriaGrouping extends Model
{
    use HasFactory;

    protected $table = 'jo_relcriteria_grouping';

    protected $fillable = [
        'groupid',
        'queryid',
        'group_condition',
        'condition_expression',
    ];
}
