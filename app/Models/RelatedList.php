<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelatedList extends Model
{
    use HasFactory;
    protected $table = 'jo_relatedlists';
    protected $primaryKey = 'relationid';
    protected $fillable = [
        'tabid',
        'related_tabid',
        'name',
        'sequence',
        'label',
        'presence',
        'actions',
        'relationfieldid',
        'source',
        'relationtype',
    ];
    public function tab()
    {
        return $this->belongsTo(Tab::class, 'tabid');
    }

    public function relatedTab()
    {
        return $this->belongsTo(Tab::class, 'related_tabid');
    }
}
