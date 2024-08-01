<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;
    protected $table='jo_documents';
    protected $fillable = [
        'id',
        'document_name',
        'documemt_url',
    ];
}
