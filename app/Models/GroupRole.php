<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupRole extends Model
{
    use HasFactory;
    protected $table='jo_group2role';
    protected $guarded=[];
    public function group()
    {
        return $this->belongsTo(Groups::class, 'groupid');
    }

    // Define the relationship with the Role model
    public function role()
    {
        return $this->belongsTo(Role::class, 'roleid');
    }
}
