<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $table='jo_roles';
    protected $fillable = ['name'];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'jo_roles2permissions');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'jo_users2roles');
    }
}
