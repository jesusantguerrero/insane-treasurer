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
        $this->publishes([__DIR__.'/../config/treasurer.php' => config_path('treasurer.php')], 'insane-paypal-config');

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


    /**
     * Configure publishing for the package.
     *
     * @return void
     */
    protected function configurePublishing()
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/treasurer.php' => config_path('treasurer.php'),
        ], 'treasurer-config');


        $this->publishes([
            __DIR__.'/../routes/treasurer.php' => base_path('routes/web.php'),
        ], 'treasurer-routes');

        $this->publishes([
            __DIR__.'/../stubs/inertia/resources/js/Pages/Billing' => resource_path('js/Pages/Billing'),
        ], 'treasurer-pages');
    }

        /**
     * Register the package resources.
     *
     * @return void
     */
     protected function registerResources()
     {
         $this->loadViewsFrom(__DIR__.'/../resources/views', 'treasurer');
     }
}
