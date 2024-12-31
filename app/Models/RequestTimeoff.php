<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequestTimeoff extends Model
{
    use HasFactory;

    // Specify the table associated with the model
    protected $table = 'jo_request_timeoff';

    // Allow mass assignment for these attributes
    protected $fillable = [
        'employee',
        'policy',
        'from',
        'to',
        'download_request_form',
        'upload_request_document',
        'description',
    ];

    // Cast JSON fields to arrays
    protected $casts = [
        'employee' => 'array',
    ];
}
