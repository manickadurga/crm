<?php

// app/Models/Action.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    use HasFactory;

    protected $table='actions';
    protected $guarded=[];
    protected $casts=[
      'action_data'=>'array'
    ];
    public function workflows()
{
    return $this->belongsToMany(Workflow::class, 'action_workflow');
}


}

