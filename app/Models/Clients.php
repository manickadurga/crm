<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    use HasFactory;
<<<<<<< HEAD
    protected $table='jo_clients';
    protected $guarded=[];
    protected $casts=[
        'projects'=>'array',
        'tags'=>'array',
        'location'=>'json',

    ];
=======
    protected $table = 'jo_clients';

    protected $fillable = [
        'image',
        'name',
        'primary_email',
        'primary_phone',
        'website',
        'fax',
        'fiscal_information',
        'projects',
        'contact_type',
        'tags',
        'location',
        'type',
        'type_suffix',
        
    ];
    protected $casts = [
        'projects' => 'array',
        'tags' => 'array',
        'location' => 'array',
    ];  
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'orgid', 'id');
    }
>>>>>>> 68e4740 (Issue -#35)

}
