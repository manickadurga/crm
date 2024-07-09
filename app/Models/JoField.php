<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoField extends Model
{
    protected $table = 'jo_fields';
    protected $primaryKey = 'fieldid';

    public function block()
    {
        return $this->belongsTo(JoBlock::class, 'block', 'blockid');
    }
}
