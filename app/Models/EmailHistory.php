<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailHistory extends Model
{
    use HasFactory;
    protected $table = 'email_history';
    protected $fillable = ['template_name', 'recipient_email', 'sent_at'];
}
