<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SharingAccess extends Model
{
    use HasFactory;

    protected $table = 'jo_sharing_access';
    protected $guarded = [];

}
