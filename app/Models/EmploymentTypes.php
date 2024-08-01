<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploymentTypes extends Model
{
    use HasFactory;

    protected $table = 'jo_employment_types';

    protected $fillable = [
        'id',
        'employment_type_name',
        'tags',
    ];
}
