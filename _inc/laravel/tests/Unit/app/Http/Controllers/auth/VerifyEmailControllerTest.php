<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Verified;
use App\Providers\RouteServiceProvider;
use Carbon\Carbon;

class VerifyEmailControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Marks the user’s email as verified and dispatches the Verified event
	 ** when an unverified user visits a valid signed verification URL.
	 **/
	public function it_marks_email_as_verified_and_dispatches_event_for_unverified_user()
	{
		Event::fake([Verified::class]);

		$user = User::factory()->create([
			'email_verified_at' => null,
		]);

		$verificationUrl = URL::temporarySignedRoute(
			'verification.verify',
			Carbon::now()->addMinutes(60),
			[
				'id'   => $user?->id,
				'hash' => sha1($user?->email),
			]
		);

		$response = $this->actingAs($user)->get($verificationUrl);

		$response->assertRedirect(RouteServiceProvider::HOME . '?verified=1');

		$this->assertNotNull($user?->fresh()->email_verified_at);
		Event::assertDispatched(Verified::class, function ($e) use ($user) {
			return $e->user->is($user);
		});
	}

	/**
	 ** @test
	 **
	 ** Redirects an already verified user back to HOME without dispatching
	 ** the Verified event, preserving the original verified timestamp.
	 **/
	public function it_redirects_for_already_verified_user_without_dispatching_event()
	{
		Event::fake([Verified::class]);

		$user = User::factory()->create([
			'email_verified_at' => now(),
		]);

		$verificationUrl = URL::temporarySignedRoute(
			'verification.verify',
			Carbon::now()->addMinutes(60),
			[
				'id'   => $user?->id,
				'hash' => sha1($user?->email),
			]
		);

		$response = $this->actingAs($user)->get($verificationUrl);

		$response->assertRedirect(RouteServiceProvider::HOME . '?verified=1');

		// email_verified_at should remain unchanged
		$this->assertEquals(
			$user?->email_verified_at->timestamp,
			$user?->fresh()->email_verified_at->timestamp
		);

		Event::assertNotDispatched(Verified::class);
	}
}
