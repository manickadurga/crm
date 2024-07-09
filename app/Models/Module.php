<?php
// app/Models/Module.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table='jo_tabs';
       protected $primaryKey = 'tabid';
    protected $fillable = ['name'];


    public function blocks()
    { return $this->hasMany(Block::class, 'tabid', 'tabid');
    }

    public function fields()
    {
        return $this->hasMany(Field::class, 'tabid', 'tabid');
    }
}
