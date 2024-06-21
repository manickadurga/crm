<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $table='jo_roles';
    protected $guarded=[];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'jo_roles2permissions');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'jo_users2roles');
    }
    public function parent()
    {
        return $this->belongsTo(Role::class, 'parentrole', 'roleid');
    }

    public function children()
    {
        return $this->hasMany(Role::class, 'parentrole', 'roleid');
    }
}
