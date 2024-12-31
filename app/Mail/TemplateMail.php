<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $templateName;
    public $body;

    public function __construct($templateName, $body)
    {
        $this->templateName = $templateName;
        $this->body = $body;
    }

    public function build()
    {
        return $this->view('emails.template')
                    ->with([
                        'templateName' => $this->templateName,
                        'body' => $this->body
                    ]);
    }
}
