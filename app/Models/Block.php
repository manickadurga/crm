<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{

    use HasFactory;
    protected $table = 'jo_blocks';
    protected $primaryKey = 'blockid';
    public $timestamps = false; 
    // protected $fillable = ['tabid', 'blocklabel'];
  
    
    protected $fillable = [
       'tabid',
       'block_label',
       'sequence',
       'show_title',
       'visible',
       'create_view',
       'edit_view',
       'detail_view',
       'display_status',
       'iscustom'

    ];

    protected $casts = [
        'show_title' => 'boolean',
        'visible' => 'boolean',
        'create_view' => 'boolean',
        'edit_view' => 'boolean',
        'detail_view' => 'boolean',
        'display_status' => 'boolean',
        'iscustom' => 'boolean',
    ];

    public function tab()
    {
        return $this->belongsTo(Tab::class, 'tabid', 'tabid');
    }
    public function fields()
    {
        return $this->hasMany(Field::class, 'block', 'blockid');
    }

    
    
    public function module()
    {
        return $this->belongsTo(Module::class, 'tabid', 'tabid');
    }

   

}
