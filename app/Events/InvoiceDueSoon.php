<?php
namespace App\Events;

use App\Models\Invoices;
use App\Models\Customers;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceDueSoon
{
    use Dispatchable, SerializesModels;

    public $invoice;
    public $contact;

    public function __construct(Invoices $invoice, Customers $contact)
    {
        $this->invoice = $invoice;
        $this->contact = $contact;
    }
}
