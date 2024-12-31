<?php 
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\InvoiceDueSoon;
use App\Models\Invoices;
use Carbon\Carbon;

class SendDueDateReminders extends Command
{
    protected $signature = 'invoices:remind-due-date';
    protected $description = 'Send reminders for invoices due tomorrow';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $invoicesDueTomorrow = Invoices::whereDate('duedate', Carbon::tomorrow())->get();
    
        foreach ($invoicesDueTomorrow as $invoice) {
            $contact = $invoice->contact; // Ensure this returns a valid Customers model instance
            
            // Debugging output
            if ($contact) {
                $this->info("Contact found: ID {$contact->id}");
            } else {
                $this->error("No contact found for invoice ID {$invoice->id}");
            }
    
            // Check if the contact is not null
            if ($contact === null) {
                continue; // Skip this iteration
            }
    
            // Fire the event to notify about the due invoice
            event(new InvoiceDueSoon($invoice, $contact));
        }
    
        $this->info('Reminders sent for invoices due tomorrow.');
    }
    
}    
