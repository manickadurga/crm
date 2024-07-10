<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    use HasFactory;
    protected $table = 'jo_incomes';

    protected $fillable = [
        'Employees that generate income',
        'Contact',
        'pick_date',
        'currency',
        'amount',
        'tags',
        'choose',
        'description',
        
    ];
    public function client()
    {
        return $this->belongsTo(Clients::class, 'contact');
    }

    /**
     * Define a relationship with jo_leads table.
     */
    public function lead()
    {
        return $this->belongsTo(Leads::class, 'contact');
    }

    /**
     * Define a relationship with jo_customers table.
     */
    public function customer()
    {
        return $this->belongsTo(Customers::class, 'contact');
    }
}

