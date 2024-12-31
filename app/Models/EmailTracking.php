<?php

// app/Models/EmailTracking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'crmid', 'mailid', 'access_count', 'click_count'
    ];
}
