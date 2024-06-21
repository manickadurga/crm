<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoBlock extends Model
{
    protected $table = 'jo_blocks';
    protected $primaryKey = 'blockid';

    public function fields()
    {
        return $this->hasMany(JoField::class, 'block', 'blockid');
    }
}
