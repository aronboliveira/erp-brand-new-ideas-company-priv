<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Console\Output\ConsoleOutput;

class DebugRouteToConsole
{
	public function handle($request, Closure $next)
	{
		$routeName  = Route::currentRouteName()   ?: '‹unnamed›';
		$routeAction = Route::currentRouteAction() ?: '‹no action›';
		$output = new ConsoleOutput();
		$output->writeln(
			"[DEBUG ROUTE] name={$routeName} action={$routeAction}"
		);
		return $next($request);
	}
}
