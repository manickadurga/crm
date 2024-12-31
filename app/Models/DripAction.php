<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DripAction extends Model
{
    use HasFactory;

    protected $table = 'drip_actions';

    protected $fillable = [
        'workflow_id',
        'batch_size',
        'current_batch_count',
        'drip_interval',
    ];

    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }
}
