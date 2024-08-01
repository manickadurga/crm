<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecuringExpenses extends Model
{
    use HasFactory;
    protected $table = 'jo_recuring_expenses';
    protected $guarded=[];
}

