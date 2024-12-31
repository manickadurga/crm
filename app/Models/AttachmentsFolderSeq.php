<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttachmentsFolderSeq extends Model
{
    use HasFactory;
    protected $table = 'jo_attachmentsfolder_seq';

    protected $guarded = [ ];
}
