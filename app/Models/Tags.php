<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    use HasFactory;
<<<<<<< HEAD
    protected $table='jo_tags';
    protected $guarded=[];
    public function customers()
    {
        return $this->belongsToMany(Customers::class, 'customer_tag', 'tag_name', 'customer_name');
    }
}
=======
      
    protected $table = 'jo_tags'; // Correct table name
    
    // Other model properties and methods
    protected $fillable = [
        'tags_names',
        'tag_color',
        'tenant_level',
        'description'
        
        
    ];
    // protected $casts = [
        // 'tags_names' => 'array',
        // 'tag_color'=>'array',
    // ]; 
}
>>>>>>> 68e4740 (Issue -#35)
