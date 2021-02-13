<?php
namespace Insane\Treasurer;

use Illuminate\Support\ServiceProvider;
use Insane\Treasurer\Console\InstallCommand;
use Insane\Treasurer\Console\SyncPlansCommand;

class TreasurerServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->publishes([__DIR__.'/../config/paypal.php' => config_path('paypal.php')], 'insane-paypal-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
                SyncPlansCommand::class
            ]);
        }
    }

    public function register()
    {

    }
}
