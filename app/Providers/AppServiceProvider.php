<?php

namespace App\Providers;

use App\Events\ContactCreated;
use App\Events\ContactTag;
use App\Events\ContactTagUpdated;
use App\Events\ContactUpdated;
use App\Events\DocumentCreated;
use App\Events\EstimateCreated;
use App\Events\TagUpdated;
use App\Events\InvoiceDueSoon;
use App\Events\InvoiceStatusChanged;
use App\Events\OpportunityCreated;
use App\Events\OpportunityStageUpdated;
use App\Events\OpportunityStatusUpdated;
use App\Events\PaymentsReceived;
use App\Events\TaskCompleted;
use App\Events\TaskCreated;
use App\Events\TaskDueDateReminder;
use App\Listeners\ContactTagUpdate;
use App\Listeners\SendNotificationsOnContactCreated;
use App\Listeners\SendNotificationsOnContactUpdated;
use App\Listeners\SendSmsOnContactCreated;
use App\Listeners\SendTagNotifications;
use App\Listeners\SendInvoiceDueNotification;
use App\Listeners\SendInvoiceStatusChangeNotification;
use App\Listeners\SendNotificationsOnContactTagUpdated;
use App\Listeners\SendNotificationsOnDocumentCreated;
use App\Listeners\SendNotificationsOnEstimateCreated;
use App\Listeners\SendNotificationsOnOpportunityCreated;
use App\Listeners\SendNotificationsOnOpportunityStageUpdated;
use App\Listeners\SendNotificationsOnOpportunityStatusUpdated;
use App\Listeners\SendNotificationsOnPaymentReceived;
use App\Listeners\SendNotificationsOnTaskCompleted;
use App\Listeners\SendTaskNotification;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
        
    }
    protected $listen = [
        'App\Events\CustomerInvited' => [
            'App\Listeners\SendInviteEmail',
        ],
        ContactCreated::class => [
            SendNotificationsOnContactCreated::class,
            //SendEmailOnContactCreated::class,
        ],
        ContactUpdated::class=>[
            SendNotificationsOnContactUpdated::class,
        ],
        TagUpdated::class=>[
            SendTagNotifications::class,
        ],
        InvoiceDueSoon::class => [
            SendInvoiceDueNotification::class,
        ],
        OpportunityStatusUpdated::class => [
            SendNotificationsOnOpportunityStatusUpdated::class,
        ],
        OpportunityCreated::class => [
            SendNotificationsOnOpportunityCreated::class,
        ],
        InvoiceStatusChanged::class => [
            SendInvoiceStatusChangeNotification::class,
        ],
        'App\Events\InvoiceStatusChanged' => [
        'App\Listeners\InvoiceStatusChangedListener',
        ],
        TaskCreated::class => [
            SendTaskNotification::class,
        ],
        'App\Events\TaskDueDateReminder' => [
        'App\Listeners\SendTaskDueDateReminder',
        ],
        OpportunityStageUpdated::class => [
            SendNotificationsOnOpportunityStageUpdated::class,
        ],
        EstimateCreated::class => [
            SendNotificationsOnEstimateCreated::class,
        ],
        ContactTag::class => [
            ContactTagUpdate::class,
        ],
        TaskCompleted::class => [
            SendNotificationsOnTaskCompleted::class,
        ],
        PaymentsReceived::class => [
            SendNotificationsOnPaymentReceived::class,
        ],
        'App\Events\DocumentCreated' => [ 
            'App\Listeners\SendNotificationsOnDocumentCreated',
        ],

        'jdavidbakr\MailTracker\Events\EmailSentEvent' => [
                        'App\Listeners\EmailSent',
],

'jdavidbakr\MailTracker\Events\ViewEmailEvent' => [
            'App\Listeners\EmailViewed',
],

'jdavidbakr\MailTracker\Events\LinkClickedEvent' => [
            'App\Listeners\EmailLinkClicked',
],
    ];
    
}
