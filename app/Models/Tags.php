<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    use HasFactory;
    protected $table='jo_tags';
    protected $guarded=[];
    public function customers()
    {
        return $this->belongsToMany(Customers::class, 'customer_tag', 'tag_name', 'customer_name');
    }
}
