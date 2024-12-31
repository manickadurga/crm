<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    protected $table = 'jo_calendar';

    protected $fillable = [
        'employee_id',
        'date',
        'start_time',
        'end_time',
        'is_billable',
        'client_id',
        'project_id',
        'team_id',
        'task_id',
        'description',
        'reason',
    ];
    protected $casts = [
        'start_time' => 'string',
        'end_time' => 'string',
    ];
    

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function client()
    {
        return $this->belongsTo(Clients::class, 'client_id');
    }

    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    public function team()
    {
        return $this->belongsTo(Teams::class, 'team_id');
    }

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }
}
