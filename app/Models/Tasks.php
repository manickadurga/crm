<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    use HasFactory;
    protected $table='jo_tasks';
    protected $guarded = [];
    
        protected $casts=[
            'tags'=>'array',
            'estimate'=>'array',
            'projects'=>'array',
            'addorremoveemployee'=>'array'
            
        ];

    public function projects()
    {
        return $this->belongsTo(Project::class);
    }

    public function contact()
{
    return $this->belongsTo(Customers::class, 'contact_id');
}
}
