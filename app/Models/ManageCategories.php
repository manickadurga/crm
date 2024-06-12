<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManageCategories extends Model
{
    use HasFactory;
    protected $table='jo_manage_categories';
    protected $guarded=[];
    protected $casts=[
        'tags'=>'array',
    ];

}
