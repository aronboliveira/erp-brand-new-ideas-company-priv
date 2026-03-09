<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{
	InteractsWithQueue,
	SerializesModels
};
use Illuminate\Support\Facades\{Auth, Cache, Log};

class RedirectWatcherJob implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	public function __construct(public string $sessionId = '#DEFAULT', public string $watcherKey = '#DEFAULT', public string $redirectTo = '/login')
	{
		Log::debug(sprintf('%s::%s created job for session %s with watcher %s for %s', __CLASS__, __FUNCTION__, $sessionId, $watcherKey, $redirectTo));
	}
	public function handle()
	{
		$watcherData = Cache::get($this->watcherKey);
		if (!$watcherData || !is_array($watcherData)) {
			Log::warning(sprintf('%s::%s watcher data not found or invalid for session %s with watcher %s', __CLASS__, __FUNCTION__, $this->sessionId, $this->watcherKey));
			return;
		}
		Log::debug(sprintf('%s::%s processing watcher data for session %s with watcher %s', __CLASS__, __FUNCTION__, $this->sessionId, $this->watcherKey), $watcherData);
		$currRoute = Cache::get('last_route_' . $this->sessionId) ?? '';
		$watcherRoute = $watcherData['current_route'] ?? '';
		if ($currRoute === $watcherRoute) {
			Log::notice(sprintf('%s::%s no route change detected for session %s with watcher %s ( %s / vs. / %s ). Terminating session and moving unauthorized user to login page.', __CLASS__, __FUNCTION__, $this->sessionId, $this->watcherKey, $currRoute, $watcherRoute));
			Cache::forget($this->watcherKey);
			Auth::logout();
			return;
		}
		Cache::forget($this->watcherKey);
	}
}
