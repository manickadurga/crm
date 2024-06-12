<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendInvitationEmails extends Command
{
    protected $signature = 'email:send-invitations';

    protected $description = 'Send invitation emails to customers in batches';

    public function handle()
    {
        $batchSize = 100; // Adjust batch size as needed
        $offset = 0;

        do {
            $customers = DB::table('jo_customers_invite')
                            ->offset($offset)
                            ->limit($batchSize)
                            ->get();

            foreach ($customers as $customer) {
                $data = [
                    'contact_name' => $customer->contact_name,
                    'primary_phone' => $customer->primary_phone,
                    'email' => $customer->email,
                    // Add more data as needed
                ];

                Mail::to($customer->email)
                    ->send(new \App\Mail\InviteMail($data));
            }

            $offset += $batchSize;
        } while (count($customers) > 0);

        $this->info('Invitation emails sent successfully!');
    }
}
