<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasFactory;
    protected $guarded=[];
    protected $table='workflows';
    protected $casts=[
        'actions_id'=>'array'
    ];
    // public function triggers()
    // {
    //     return $this->hasMany(WorkflowTrigger::class);
    // }

    // public function actions()
    // {
    //     return $this->hasMany(WorkflowAction::class);
    // }
    // public function customers()
    // {
    //     return $this->belongsToMany(Customers::class, 'jo_workflow_contacts', 'workflow_id', 'customer_id');
    // }
    // public function customer()
    // {
    //     return $this->belongsTo(Customers::class); // Ensure this matches your actual Customer model
    // }
    public function trigger()
    {
        return $this->belongsTo(Trigger::class);
    }
    public function actions()
    {
        return $this->belongsTo(Action::class);
    }

    public function dripAction()
    {
        return $this->hasOne(DripAction::class, 'workflow_id');
    }
}
