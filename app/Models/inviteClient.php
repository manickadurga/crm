<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class inviteClient extends Model
{
    use HasFactory;
    protected $tables = 'joinvite_clients';
    protected $fillable = [
        'contact_name', 'primary_phone', 'email',
    ];
}
