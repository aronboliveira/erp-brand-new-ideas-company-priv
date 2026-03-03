<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;
use App\Helpers\SafeConsoleOutput;

class DebugRouteToConsole
{
	public function handle($request, Closure $next)
	{
		$routeName  = Route::currentRouteName()   ?: '‹unnamed›';
		$routeAction = Route::currentRouteAction() ?: '‹no action›';
		$output = SafeConsoleOutput::make();
		$output->writeln(
			"[DEBUG ROUTE] name={$routeName} action={$routeAction}"
		);
		return $next($request);
	}
}
