<?php

namespace App\Jobs;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Modules\Customers\Models\Customer as ModelsCustomer;

class SendDripEmailsJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $batchSize;
    protected $nextRunTime;

    /**
     * Create a new job instance.
     *
     * @param int $batchSize
     * @param \Carbon\Carbon $nextRunTime
     * @return void
     */
    public function __construct(int $batchSize, $nextRunTime)
    {
        Log::info("Initializing SendDripEmailsJob with batch size: {$batchSize}, next run time: {$nextRunTime}");
        $this->batchSize = $batchSize;
        $this->nextRunTime = $nextRunTime;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("Running drip email job with batch size: {$this->batchSize}, next run time: {$this->nextRunTime}");

        // Retrieve contacts who need to receive the emails
        $contacts = ModelsCustomer::limit($this->batchSize)->get(); // You may need to adjust this based on your criteria
        Log::info("Retrieved " . $contacts->count() . " contacts for the current batch.");
        if ($contacts->isEmpty()) {
            Log::info("No contacts available to send drip emails.");
            return;
        }

        foreach ($contacts as $contact) {
            // Send the email
            $this->sendDripEmailToContact($contact);

            // Optionally, track the time when the email was sent
            $contact->last_drip_email_sent_at = now();
            $contact->save();
        }

        // After processing the current batch, we schedule the next batch
        $this->scheduleNextDripBatch();
    }

    /**
     * Send the drip email to an individual contact.
     *
     * @param \App\Models\Customer $contact
     * @return void
     */
    protected function sendDripEmailToContact($contact)
    {
        Log::info("Sending drip email to contact ID: {$contact->id}, Email: {$contact->email}");

        // Define the email details
        $emailData = [
            'from_name' => 'Support',
            'from_email' => 'support@example.com',
            'subject' => 'Welcome to our Service!',
            'message' => 'Hello, Welcome to our service! Thank you for registering!',
        ];

        // Send the email
        Mail::send([], [], function ($message) use ($contact, $emailData) {
            $message->to($contact->primary_email)
                    ->from($emailData['from_email'], $emailData['from_name'])
                    ->subject($emailData['subject'])
                    ->setBody($emailData['message'], 'text/html');
        });

        Log::info("Drip email sent to contact ID: {$contact->id}, Email: {$contact->email}");
    }

    /**
     * Schedule the next batch of drip emails.
     *
     * @return void
     */
    protected function scheduleNextDripBatch()
    {
        // Schedule the next batch based on the next run time
        $delay = $this->nextRunTime->diffInSeconds(now()); // Calculate the delay in seconds

        // Dispatch the job to run at the specified time
        SendDripEmailsJob::dispatch($this->batchSize, $this->nextRunTime)
            ->delay(now()->addSeconds($delay));

        Log::info("Next drip email batch scheduled.");
    }
}
