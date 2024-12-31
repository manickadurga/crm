<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowTrigger extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $table='jo_workflow_triggers';
    protected $casts = [
        'filters' => 'array',  // Automatically cast JSON to array
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}
