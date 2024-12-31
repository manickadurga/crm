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

        'projects' => 'json',
        'tags' => 'array',
        //'location' => 'json',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'contact_user', 'contact_id', 'user_id');
    }

}

