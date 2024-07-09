<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table ='jo_organizations';
    protected $fillable = [
        'image',
        'organization_name',
        'currency',
        'official_name',
        'tax_id',
        'tags',
        'location',
        'employee_bonus_type',
        'choose_time_zone',
        'start_week_on',
        'default_date_type',
        'regions',
        'select_number_format',
        'date_format',
        'fiscal_year_start_date',
        'fiscal_year_end_date',
        'enable_disable_invites',
        'invite_expiry_period',
        
    ];

    protected $casts = [
        'location' => 'array', // Cast the location attribute to an array
    ];
   
}