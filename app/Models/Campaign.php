<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'template_id',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
    public function details()
    {
        return $this->hasOne(CampaignDetail::class);
    }
}
