<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $table = 'jo_approvals';

    protected $fillable = [
        'name',
        'min_count',
        'approval_policy',
        'created_by',
        'created_at',
        'employees',
        'teams',
        'status',
    ];

    // Add any relationships, accessors, or other custom methods here
}
