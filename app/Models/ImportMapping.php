<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportMapping extends Model
{
    use HasFactory;
    protected $fillable = ['module', 'csv_header', 'crm_field', 'default_value'];
}
