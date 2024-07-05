<?php

namespace App\Models;

use Illuminate\Console\View\Components\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estimate extends Model
{
    use HasFactory;

    protected $table = 'jo_estimates';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function tasks()
    {
        // Assuming there's a Task model related to Estimate
        return $this->hasMany(Tasks::class);
    }
}