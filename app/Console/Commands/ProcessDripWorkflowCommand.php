<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DripService;

class ProcessDripWorkflowCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-drip-workflow-command';
    protected $description = 'Process drip workflow for the given workflow ID';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $workflowId = $this->argument('workflowId');
        $dripService = new DripService();
        $dripService->processDripWorkflow($workflowId);

        $this->info("Drip workflow processed for workflow ID: {$workflowId}");
    }
}
