<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JoTab extends Model
{
    protected $table = 'jo_tabs';
    protected $primaryKey = 'tabid';

    // Define relationship to JoBlock
    public function blocks()
    {
        return $this->hasMany(JoBlock::class, 'tabid', 'tabid');
    }
}
