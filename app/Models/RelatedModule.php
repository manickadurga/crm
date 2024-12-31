<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelatedModule extends Model
{
    use HasFactory;
    protected $table = 'jo_datashare_relatedmodules';

    protected $primaryKey = 'datashare_relatedmodule_id';

    protected $fillable = [
        'tabid',
        'relatedto_tabid',
    ];

    public $timestamps = true;
}
