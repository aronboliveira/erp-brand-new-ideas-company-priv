<?php

namespace Tests\Unit\app\Http\Controllers;

use App\Models\User;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Gate, View as ViewFacade};
use Mockery;

/**
 * Shared helpers for controller unit tests.
 */
trait ControllerTestHelper
{
	/**
	 * Create a real-enough User mock without DB.
	 */
	protected function makeMockUser(int $id = 1, int $creatorId = 1, string $type = 'company'): User
	{
		$user = Mockery::mock(User::class)->makePartial()->shouldIgnoreMissing();
		$user->id = $id;
		$user->email = "testuser{$id}@example.com";
		$user->name = "Test User {$id}";
		$user->type = $type;
		$user->is_banned = 0;
		$user->shouldReceive('creatorId')->andReturn($creatorId);
		$user->shouldReceive('can')->andReturn(true);
		$user->shouldReceive('hasPermissionTo')->andReturn(true);
		$user->shouldReceive('hasVerifiedEmail')->andReturn(true);
		$user->shouldReceive('getAuthIdentifier')->andReturn($id);
		$user->shouldReceive('getAuthIdentifierName')->andReturn('id');
		$user->shouldReceive('getAuthPassword')->andReturn('hashed');
		$user->shouldReceive('getRememberToken')->andReturn(null);
		$user->shouldReceive('getRememberTokenName')->andReturn('remember_token');
		return $user;
	}

	/**
	 * Log in a mock user using actingAs (no facade mocking).
	 */
	protected function loginMockUser(?int $id = 1, ?int $creatorId = 1, string $type = 'company'): User
	{
		$user = $this->makeMockUser($id, $creatorId, $type);
		$this->actingAs($user);
		return $user;
	}

	/**
	 * Build a synthetic Request object.
	 */
	protected function makeRequest(
		string $uri = '/',
		string $method = 'GET',
		array $data = [],
		bool $wantsJson = false
	): Request {
		$server = $wantsJson ? ['HTTP_ACCEPT' => 'application/json'] : [];
		return Request::create($uri, $method, $data, [], [], $server);
	}

	/**
	 * Assert the value is a redirect.
	 */
	protected function assertIsRedirect($response, ?string $containsUrl = null): void
	{
		$this->assertInstanceOf(RedirectResponse::class, $response);
		if ($containsUrl !== null) {
			$this->assertStringContainsString($containsUrl, $response->getTargetUrl());
		}
	}
}
