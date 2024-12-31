<?php

namespace App\Jobs;

use App\Mail\CampaignMailable;
use App\Models\EmailHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class BatchScheduleEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $templateName;
    protected $body;
    protected $senderName;
    protected $subjectLine;
    protected $previewText;
    protected $recipientEmails; // Array of recipient emails

    public function __construct($templateName, $body, $senderName, $subjectLine, $previewText, $recipientEmails)
    {
        $this->templateName = $templateName;
        $this->body = $body;
        $this->senderName = $senderName;
        $this->subjectLine = $subjectLine;
        $this->previewText = $previewText;
        $this->recipientEmails = $recipientEmails; // Store the array of recipient emails
    }

    public function handle()
    {
        // Log the email sending process for each recipient
        foreach ($this->recipientEmails as $recipientEmail) {
            try {
                Log::info("Sending batch email to: {$recipientEmail}");

                // Create a new mailable instance
                $mailable = new CampaignMailable(
                    $this->templateName,
                    $this->body,
                    $this->senderName,
                    $this->subjectLine,
                    $this->previewText,
                    $recipientEmail // Pass individual recipient email
                );

                // Send the email
                Mail::send($mailable);

                EmailHistory::create([
                    'recipient_email' => $recipientEmail,
                    'template_name' => $this->templateName,
                    'sent_at' => now(), // Optionally, set this explicitly if needed
                ]);
                

                Log::info("Email successfully sent to: {$recipientEmail}");
            } catch (\Exception $e) {
                Log::error("Mail sending failed for {$recipientEmail}: " . $e->getMessage());
            }
        }
    }
}
