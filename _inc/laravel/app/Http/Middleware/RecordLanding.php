<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RecordLanding
{
	public function handle(Request $request, Closure $next)
	{
		return $next($request);
	}

	public function terminate(Request $request, $response)
	{
		if (
			$request->getMethod() === 'GET'
			&& $response->getStatusCode() < 400
			&& $routeName = $request->route()?->getName()
		) {
			$key = 'last_route_' . session()->getId();
			Cache::put($key, ['name' => $routeName], now()->addMinutes(30));
		}
	}
}
