<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;
    protected $table = 'jo_organizations';
     protected $primaryKey = 'organizationid';
    // Define the fields that can be mass-assigned
    protected $fillable = [
        'organization_name',
        'currency',
        'official_name',
        'tax_id',
        'tags',
        'find_address',
        'country',
        'city',
        'postcode',
        'address',
        'address_2',
        'employee_bonus_type',
        'bonus_percentage',
        'timezone',
        'start_week_on',
        'default_date_type',
        'regions',
        'number_format',
        'date_format',
        'fiscal_year_start_date',
        'fiscal_year_end_date',
        'invite_expiry_period',
        'allow_users_to_send_invites',
    ];
}
