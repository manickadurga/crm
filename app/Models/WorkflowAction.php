<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowAction extends Model
{
    use HasFactory;
    protected $table='jo_workflow_actions';
    protected $guarded=[];
    protected $casts = [
        'action_data' => 'array',  // Automatically cast JSON to array
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}
