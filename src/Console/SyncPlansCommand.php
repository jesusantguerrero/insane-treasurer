<?php

namespace Insane\Treasurer\Console;

use Illuminate\Console\Command;
use Insane\Treasurer\PaypalServiceV2;

class SyncPlansCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'treasurer:sync-plans {userId}';

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
        $this->syncPlans($this->argument('userId'));
    }


    /**
     * Install the Inertia stack into the application.
     *
     * @return void
     */
    protected function syncPlans($userId)
    {
        print_r($userId);
        $paypalService = new PaypalServiceV2();
        $paypalService->syncPlans($userId);
    }
}
