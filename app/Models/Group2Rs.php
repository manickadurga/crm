<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group2Rs extends Model
{
    use HasFactory;

    protected $table = 'jo_group2rs';

    protected $guarded=[];

    // Define relationships
    public function group()
    {
        return $this->belongsTo(Groups::class, 'groupid', 'id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'roleandsubid', 'roleid');
    }
}
