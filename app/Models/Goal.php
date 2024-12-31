<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id', 'type', 'status', 'target_value'
    ];

    // Optional: Define relationships if any, e.g., with Workflow model
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }
}
