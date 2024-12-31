<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WorkflowMail extends Mailable
{
    use Queueable, SerializesModels;

    public $actionData;
    public $contact;

    /**
     * Create a new message instance.
     *
     * @param array $actionData Email content, subject, etc.
     * @param array $contact Contact details (name, email)
     */
    public function __construct($actionData, $contact)
    {
        $this->actionData = $actionData;
        $this->contact = $contact;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Replace any placeholders in the message, such as {{contact.name}}
        $messageBody = str_replace('{{contact.name}}', $this->contact['name'], $this->actionData['message']);

        // Build the email
        $email = $this->subject($this->actionData['subject'])
                      ->from($this->actionData['from_email'], $this->actionData['from_name'])
                      ->view('emails.workflow')  // Create a Blade view for your email content
                      ->with(['messageBody' => $messageBody]);

        // Add attachments if any are present
        if (!empty($this->actionData['attachments'])) {
            foreach ($this->actionData['attachments'] as $attachment) {
                $email->attach($attachment['url']);
            }
        }

        return $email;
    }
}
