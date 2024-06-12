<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoField extends Model
{
    use HasFactory;
    protected $table = 'jo_fields';
    protected $fillable=['jo_customers'];
}
