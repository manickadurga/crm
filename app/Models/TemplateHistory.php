<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateHistory extends Model
{
    use HasFactory;
    protected $table = 'jo_templates';
    protected $fillable = ['template_name'];

    // If you need to disable the 'updated_at' timestamp
    public $timestamps = ['created_at'];

    // You can also specify custom date formats if necessary
    protected $dateFormat = 'Y-m-d H:i:s';
}
