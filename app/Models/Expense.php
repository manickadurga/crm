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
    public function vendor()
    {
        return $this->belongsTo(Vendors::class, 'vendors');
    }

    public function category()
    {
        return $this->belongsTo(ManageCategories::class, 'categories');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employees_that_generate');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'projects');
    }
}

