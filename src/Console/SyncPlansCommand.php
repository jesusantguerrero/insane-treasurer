<?php

namespace Insane\Treasurer\Console;

use Illuminate\Console\Command;
use Insane\Treasurer\BillingService;

class SyncPlansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'treasurer:sync-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync paypal plans';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->syncPlans();
    }


    /**
     * Install the Inertia stack into the application.
     *
     * @return void
     */
    protected function syncPlans()
    {
        $service = new BillingService();
        $service->syncPlans();
    }
}
