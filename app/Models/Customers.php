<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customers extends Model
{
    use HasFactory;

    protected $table = 'jo_customers';
    protected $guarded = [];
    protected $casts = [
        'projects' => 'array',
        'tags' => 'json',
        'location' => 'json',
    ];
    // public function getProjectsAttribute()
    // {
    //     return Projects::whereIn('id', json_decode($this->attributes['projects']))->get();
    // }

    // public function getTagsAttribute()
    // {
    //     return Tags::whereIn('id', json_decode($this->attributes['tags']))->get();
    // }

}
