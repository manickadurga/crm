<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = "jo_expenses";
    protected $casts=[
        'contact'=>'array'
    ];
}
