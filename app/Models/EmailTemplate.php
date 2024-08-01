<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;
    protected $table='jo_email_templates';

    protected $fillable = [
        'template_name',
        'description',
        'select_module',
        'select_field',
        'subject',
        'email_content'
    ];


}
