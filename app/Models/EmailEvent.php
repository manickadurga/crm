<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailEvent extends Model
{
    use HasFactory;

    protected $fillable = ['email_id', 'event_type', 'event_data', 'timestamp'];
       

}
