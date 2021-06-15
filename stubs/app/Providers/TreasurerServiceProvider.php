<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Insane\Treasurer\TreasurerFacade;

class TreasurerServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
       TreasurerFacade::billable(Team::class)->resolve(function (Request $request) {
        return $request->user()->currentTeam;
       });
    }
}
