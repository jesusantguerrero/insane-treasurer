<?php

namespace Insane\Treasurer\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;

class EnsureIsBiller
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $currentUser = $request->user();
        if (!$currentUser->ownsTeam($currentUser->currentTeam)) {
            return Redirect(RouteServiceProvider::HOME);
        }
        return $next($request);
    }
}
