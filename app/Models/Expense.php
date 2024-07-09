<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $table='jo_expenses';
    protected $guarded=[];
    protected $casts=[
      'projects'=>'array',
      'tags'=>'array',
      'contacts'=>'array',
      //'currency'=>'array'
    ];
}

