<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkflowContact extends Model
{
    use HasFactory;
    protected $table = 'jo_workflow_contacts'; // Ensure this matches your database table

    protected $fillable = [
        'workflow_id',
        'contact_id',
    ];

    // Enable timestamps if needed
    // public $timestamps = true; // Uncomment if you want to use created_at and updated_at

    /**
     * Define the relationship with Workflow
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class, 'workflow_id'); // Specify the foreign key if different
    }

    /**
     * Define the relationship with Customer
     */
    public function contact()
    {
        return $this->belongsTo(Customers::class, 'contact_id'); // Specify the foreign key if different
    }

}
