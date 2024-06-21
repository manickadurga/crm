<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile2Tab extends Model
{
    use HasFactory;
    protected $table='jo_profile2tab';
    protected $guarded=[];
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profileid', 'profileid');
    }

    public function tab()
    {
        return $this->belongsTo(Tab::class, 'tabid', 'tabid');
    }
    public function isEditable()
    {
        return $this->permissions !== 1;
    }
    public function updatePermissions($newPermissions)
    {
        // Check if the current permissions allow editing
        if ($this->isEditable()) {
            $this->permissions = $newPermissions;
            return $this->save();
        }

        return false;
    }
}
