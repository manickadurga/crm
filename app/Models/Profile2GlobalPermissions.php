<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile2GlobalPermissions extends Model
{
    use HasFactory;

    protected $table = 'jo_profile2globalpermissions';

    protected $guarded=[];

    // Define the relationship with the Profile model
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profileid');
    }
}
