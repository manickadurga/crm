<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignDetail extends Model
{
    use HasFactory;

    // Specify the table name if it's not the plural of the model name
    protected $table = 'campaign_details';

    // Specify the fillable fields
    protected $fillable = [
        'campaign_id',
        'send_method',
        'sender_email',
        'sender_name',
        'subject_line',
        'preview_text',
        'recipient_to',
        'schedule_at',
        'start_on',
        'no_of_recipients',
        'batch_quantity',
        'repeat_after',
        'send_on',
        'start_time',
        'end_time',
    ];

    // Define relationships if needed
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
