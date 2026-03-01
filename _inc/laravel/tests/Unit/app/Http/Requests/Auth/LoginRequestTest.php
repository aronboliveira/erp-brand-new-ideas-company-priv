<?php

namespace Tests\Unit\Http\Requests\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Auth\LoginRequest;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class LoginRequestTest extends TestCase
{
	use RefreshDatabase, MockeryPHPUnitIntegration;

	/**
	 ** @test
	 **
	 ** authorize() still returns true for non-HTTPS
	 ** (local/non-production), but logs appropriately.
	 **/
	public function authorize_returns_true_for_non_https()
	{
		$base = Request::create('http://example.com/login', 'POST');
		$req = LoginRequest::createFromBase($base);

		$this->assertTrue($req->authorize());
	}

	/**
	 ** @test
	 **
	 ** authorize() returns true for HTTPS requests.
	 **/
	public function authorize_returns_true_for_https()
	{
		$base = Request::create('https://example.com/login', 'POST');
		$req = LoginRequest::createFromBase($base);

		$this->assertTrue($req->authorize());
	}

	/**
	 ** @test
	 **
	 ** prepareForValidation() trims and lowercases email, trims password.
	 **/
	public function prepare_for_validation_normalizes_input()
	{
		$data = ['email' => '  Foo@Bar.COM ', 'password' => '  secret  '];
		$base = Request::create('https://example.com/login', 'POST', $data);
		$req = LoginRequest::createFromBase($base);

		$method = new \ReflectionMethod(LoginRequest::class, 'prepareForValidation');
		$method->setAccessible(true);
		$method->invoke($req);

		$this->assertSame('foo@bar.com', $req->input('email'));
		$this->assertSame('secret', $req->input('password'));
	}

	/**
	 ** @test
	 **
	 ** rules() returns the correct validation rules.
	 **/
	public function rules_return_expected_rules()
	{
		$req = new LoginRequest();
		$rules = $req->rules();

		$this->assertArrayHasKey('email', $rules);
		$this->assertContains('required', $rules['email']);
		$this->assertContains('email', $rules['email']);

		$this->assertArrayHasKey('password', $rules);
		$this->assertContains('required', $rules['password']);
		$this->assertContains('min:8', $rules['password']);
		$this->assertContains('max:128', $rules['password']);
	}

	/**
	 ** @test
	 **
	 ** throttleKey() combines lowercased email and client IP.
	 **/
	public function throttle_key_is_email_lowercase_and_ip()
	{
		$base = Request::create('https://example.com/login', 'POST', ['email' => 'User@Example.COM']);
		$base->server->set('REMOTE_ADDR', '1.2.3.4');
		$req = LoginRequest::createFromBase($base);

		$key = $req->throttleKey();
		$this->assertSame('user@example.com|1.2.3.4', $key);
	}

	/**
	 ** @test
	 **
	 ** ensureIsNotRateLimited() does nothing when under the limit.
	 **/
	public function ensure_is_not_rate_limited_passes_when_under_limit()
	{
		$req = \Mockery::mock(LoginRequest::class . '[throttleKey]')->makePartial();
		$req->shouldReceive('throttleKey')->andReturn('key');

		RateLimiter::shouldReceive('tooManyAttempts')->with('key', 5)->andReturnFalse();

		$req = new \App\Http\Requests\Auth\LoginRequest();
		$req->merge([
			'email'    => 'foo@example.com',
			'password' => 'secret',
		]);
		$req->ensureIsNotRateLimited();
	}

	/**
	 ** @test
	 **
	 ** ensureIsNotRateLimited() throws ValidationException when rate limited.
	 **/
	public function ensure_is_not_rate_limited_throws_on_too_many_attempts()
	{
		$req = \Mockery::mock(LoginRequest::class . '[throttleKey]')->makePartial();
		$req->shouldReceive('throttleKey')->andReturn('key');

		RateLimiter::shouldReceive('tooManyAttempts')->with('key', 5)->andReturnTrue();
		RateLimiter::shouldReceive('availableIn')->with('key')->andReturn(120);
		Event::fake();

		$this->expectException(ValidationException::class);
		$req = new \App\Http\Requests\Auth\LoginRequest();
		$req->merge([
			'email'    => 'foo@example.com',
			'password' => 'secret',
		]);
		$req->ensureIsNotRateLimited();
	}

	/**
	 ** @test
	 **
	 ** authenticate() throws ValidationException on failed credentials.
	 **/
	public function authenticate_throws_on_failed_credentials()
	{
		$base = Request::create('https://example.com/login', 'POST', [
			'email'    => 'fail@example.com',
			'password' => 'wrong',
		]);
		$req = LoginRequest::createFromBase($base);

		RateLimiter::shouldReceive('tooManyAttempts')->andReturnFalse();
		Auth::shouldReceive('attempt')
			->with(['email' => 'fail@example.com', 'password' => 'wrong'], false)
			->andReturnFalse();
		RateLimiter::shouldReceive('hit')->once()->with($req->throttleKey());

		$this->expectException(ValidationException::class);
		$req->authenticate();
	}

	/**
	 ** @test
	 **
	 ** authenticate() succeeds and clears rate limiter on valid credentials.
	 **/
	public function authenticate_succeeds_on_valid_credentials()
	{
		$base = Request::create('https://example.com/login', 'POST', [
			'email'    => 'user@example.com',
			'password' => 'right',
			'remember' => true,
		]);
		$req = LoginRequest::createFromBase($base);

		RateLimiter::shouldReceive('tooManyAttempts')->andReturnFalse();
		Auth::shouldReceive('attempt')
			->with(['email' => 'user@example.com', 'password' => 'right'], true)
			->andReturnTrue();
		Session::shouldReceive('regenerate')->once();
		RateLimiter::shouldReceive('clear')->once()->with($req->throttleKey());

		// No exception should be thrown
		$req->authenticate();
		$this->assertTrue(true);
	}
}
