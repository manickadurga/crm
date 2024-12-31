<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalPolicy extends Model
{
    use HasFactory;

    // Specify the table name if different from the default convention
    protected $table = 'jo_approval_policy';

    // Specify the attributes that are mass assignable
    protected $fillable = [
        'name',
        'description',
    ];
}