<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    protected $table = 'purchases';
    protected $guarded = [];

    protected static function newFactory(): PurchaseFactory
    {
        // return PurchaseFactory::new();
    }
}