<?php

namespace App\Jobs;

use App\Mail\CampaignMailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class SendScheduledMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $templateName;
    protected $body;
    protected $senderName;
    protected $subjectLine;
    protected $previewText;
    protected $recipientEmail;

    public function __construct($templateName, $body, $senderName, $subjectLine, $previewText, $recipientEmail)
    {
        $this->templateName = $templateName;
        $this->body = $body;
        $this->senderName = $senderName;
        $this->subjectLine = $subjectLine;
        $this->previewText = $previewText;
        $this->recipientEmail = $recipientEmail; // Store recipient email
    }

    public function handle()
    {
        $mailable = new CampaignMailable(
            $this->templateName,
            $this->body,
            $this->senderName,
            $this->subjectLine,
            $this->previewText,
            $this->recipientEmail // Pass it to the mailable
        );

        Mail::send($mailable);
    }
}
