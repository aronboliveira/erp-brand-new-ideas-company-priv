<?php

namespace Tests\Unit\app\Http\Controllers\views;

/**
 * Shared view-assertion helpers for controller view tests.
 */
trait ViewAssertionHelper
{
	protected function getViewsPath(): string
	{
		return base_path('resources/views');
	}

	/**
	 * Assert that a Blade view file exists on disk given its dot-notation path.
	 */
	protected function assertBladeViewExists(string $dotPath): void
	{
		$filePath = $this->getViewsPath() . '/' . str_replace('.', '/', $dotPath) . '.blade.php';
		$this->assertFileExists($filePath, "Blade view [{$dotPath}] not found at {$filePath}");
	}

	/**
	 * Assert the HTTP response is successful and optionally matches the expected view.
	 */
	protected function assertViewResponse($response, string $expectedView = ''): void
	{
		$response->assertSuccessful();
		if ($expectedView) {
			$response->assertViewIs($expectedView);
		}
	}

	/**
	 * Perform an authenticated GET request using a mock super-admin user.
	 */
	protected function safeGet(string $uri): \Illuminate\Testing\TestResponse
	{
		$user = $this->makeMockUser(type: 'super admin');
		$this->actingAs($user);
		return $this->get($uri);
	}

	/**
	 * Perform a safe GET that tolerates non-500 failures (e.g. redirects due to missing DB data).
	 *
	 * @return \Illuminate\Testing\TestResponse
	 */
	protected function tolerantGet(string $uri): \Illuminate\Testing\TestResponse
	{
		$user = $this->makeMockUser(type: 'super admin');
		$this->actingAs($user);
		$response = $this->get($uri);
		$this->assertNotEquals(
			500,
			$response->getStatusCode(),
			"Route [{$uri}] returned HTTP 500 — likely a server error, not just missing data."
		);
		return $response;
	}
}
