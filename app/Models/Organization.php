<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Factories\HasFactory;
=======
>>>>>>> 68e4740 (Issue -#35)
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
<<<<<<< HEAD
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
=======
    protected $table="jo_organizations";
    protected $fillable = [
        'organization_name', 'official_name', 'tags', 'location', 'employee_bonus_type',
        'choose_time_zone', 'start_week_on', 'default_date_type', 'regions',
        'select_number_format', 'date_format', 'fiscal_year_start_date', 'fiscal_year_end_date',
        'enable_disable_invites', 'invite_expiry_period', 'primary_email', 'currency', 'tax_id',
    ];

    protected $casts = [
        'location' => 'json',
        'tags' => 'json',
    ];

    // Optional: Define relationships if any
>>>>>>> 68e4740 (Issue -#35)
}
