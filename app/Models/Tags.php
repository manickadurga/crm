<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    use HasFactory;
      
    protected $table = 'jo_tags'; // Correct table name
    
    // Other model properties and methods
    protected $fillable = [
        'id',
        'tags_name',
        'tag_color',
        'tenant_level',
        'description'
        
        
    ];
    // protected $casts = [
        // 'tags_names' => 'array',
        // 'tag_color'=>'array',
    // ]; 
}