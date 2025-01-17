<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tab extends Model
{
    use HasFactory;
    protected $table = 'jo_tabs';
    protected $primaryKey = 'tabid';
    protected $guarded = [];
    public function fields()
    {
        return $this->hasMany(Field::class, 'tabid', 'tabid');
    }
    public function blocks()
    {
        return $this->hasMany(Block::class, 'tabid', 'tabid');
    }
}