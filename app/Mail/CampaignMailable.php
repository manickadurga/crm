<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CampaignMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $templateName;
    public $body;
    public $senderName;
    public $previewText;
    public $subjectLine;
    public $recipientEmail;

    public function __construct($templateName, $body, $senderName, $subjectLine, $previewText, $recipientEmail)
    {
        $this->templateName = $templateName;
        $this->body = $body;
        $this->senderName = $senderName;
        $this->subjectLine = $subjectLine;
        $this->previewText = $previewText;
        $this->recipientEmail = $recipientEmail;
    }

    public function build()
    {
        Log::info('Building email with data', [
            'templateName' => $this->templateName,
            'subjectLine' => $this->subjectLine,
            'senderName' => $this->senderName,
            'body' => $this->body,
        ]);
        return $this->view('emails.template') // Ensure this points to your Blade view
            ->subject($this->subjectLine)
            ->from(config('mail.from.address'), $this->senderName)
            ->to($this->recipientEmail)
            ->with([
                'templateName' => $this->templateName,
                'body' => $this->body,
            ]);
    }
}