<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AddHolidays extends Model
{
    use HasFactory;
    protected $table = 'jo_add_holidays';

    // Allow mass assignment for these attributes
    protected $fillable = [
        'holiday_name',
        'employee',
        'policy',
        'from',
        'to',
    ];

    // Cast JSON fields to arrays
    protected $casts = [
        'employee' => 'array',
    ];
}
