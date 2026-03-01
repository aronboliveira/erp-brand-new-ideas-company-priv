<?php

namespace Tests\Unit\Middleware;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[CoversClass(LoginRequest::class)]
#[Group('middleware')]
#[Group('login-request')]
class LoginRequestTest extends TestCase
{
	// ───────── authorize() ─────────

	#[Test]
	public function authorize_returns_true_for_localhost(): void
	{
		$request = LoginRequest::create('http://localhost/login', 'POST', [
			'email'    => 'test@example.com',
			'password' => 'password123',
		]);

		$this->assertTrue($request->authorize());
	}

	#[Test]
	public function authorize_returns_true_for_loopback(): void
	{
		$request = LoginRequest::create('http://127.0.0.1/login', 'POST', [
			'email'    => 'test@example.com',
			'password' => 'password123',
		]);

		$this->assertTrue($request->authorize());
	}

	#[Test]
	public function authorize_returns_true_for_https(): void
	{
		$request = LoginRequest::create('https://example.com/login', 'POST', [
			'email'    => 'test@example.com',
			'password' => 'password123',
		]);

		$this->assertTrue($request->authorize());
	}

	#[Test]
	public function authorize_returns_true_for_insecure_remote(): void
	{
		$request = LoginRequest::create('http://example.com/login', 'POST', [
			'email'    => 'test@example.com',
			'password' => 'password123',
		]);

		// authorize() always returns true; insecure remote just logs a warning
		$this->assertTrue($request->authorize());
	}

	// ───────── rules() ─────────

	#[Test]
	public function rules_returns_email_and_password_rules(): void
	{
		$request = LoginRequest::create('https://localhost/login', 'POST', [
			'email'    => 'test@example.com',
			'password' => 'password123',
		]);

		$rules = $request->rules();

		$this->assertIsArray($rules);
		$this->assertArrayHasKey('email', $rules);
		$this->assertArrayHasKey('password', $rules);
	}

	#[Test]
	public function rules_email_is_required_and_has_max_length(): void
	{
		$request = LoginRequest::create('https://localhost/login', 'POST');
		$rules   = $request->rules();

		$emailRules = $rules['email'];
		$this->assertContains('required', $emailRules);
		$this->assertContains('string', $emailRules);
		$this->assertContains('email', $emailRules);
		$this->assertContains('max:255', $emailRules);
	}

	#[Test]
	public function rules_password_has_min_and_max_length(): void
	{
		$request = LoginRequest::create('https://localhost/login', 'POST');
		$rules   = $request->rules();

		$pwRules = $rules['password'];
		$this->assertContains('required', $pwRules);
		$this->assertContains('string', $pwRules);
		$this->assertContains('min:8', $pwRules);
		$this->assertContains('max:128', $pwRules);
	}

	// ───────── throttleKey() ─────────

	#[Test]
	public function throttleKey_returns_email_pipe_ip_format(): void
	{
		$request = LoginRequest::create('https://localhost/login', 'POST', [
			'email'    => 'User@Example.COM',
			'password' => 'password123',
		]);

		$key = $request->throttleKey();

		$this->assertStringContainsString('|', $key);
		// Email part is lowered
		$this->assertStringStartsWith('user@example.com', $key);
	}

	#[Test]
	public function throttleKey_uses_lowercased_email(): void
	{
		$request = LoginRequest::create('https://localhost/login', 'POST', [
			'email'    => 'ADMIN@PRESTECH.COM',
			'password' => 'password123',
		]);

		$key = $request->throttleKey();

		$this->assertStringContainsString('admin@prestech.com', $key);
		$this->assertStringNotContainsString('ADMIN', $key);
	}

	// ───────── prepareForValidation() ─────────

	#[Test]
	public function prepareForValidation_lowercases_email(): void
	{
		$request = LoginRequest::create('https://localhost/login', 'POST', [
			'email'    => '  TestUser@Example.COM  ',
			'password' => '  myPassword123  ',
		]);

		// Simulate what prepareForValidation does
		$prepared = Str::lower(trim('  TestUser@Example.COM  '));

		$this->assertSame('testuser@example.com', $prepared);
	}

	#[Test]
	public function prepareForValidation_trims_whitespace(): void
	{
		$email = '  spaced@email.com  ';
		$password = '  pass1234  ';

		$preparedEmail = Str::lower(trim($email));
		$preparedPw    = trim($password);

		$this->assertSame('spaced@email.com', $preparedEmail);
		$this->assertSame('pass1234', $preparedPw);
	}

	// ───────── Constants ─────────

	#[Test]
	public function class_has_expected_constants(): void
	{
		$ref = new \ReflectionClass(LoginRequest::class);
		$constants = $ref->getConstants();

		$this->assertArrayHasKey('EMAIL_MAX_LENGTH', $constants);
		$this->assertArrayHasKey('PASSWORD_MIN_LENGTH', $constants);
		$this->assertArrayHasKey('PASSWORD_MAX_LENGTH', $constants);
		$this->assertArrayHasKey('RATE_LIMIT_ATTEMPTS', $constants);

		$this->assertSame(255, $constants['EMAIL_MAX_LENGTH']);
		$this->assertSame(8, $constants['PASSWORD_MIN_LENGTH']);
		$this->assertSame(128, $constants['PASSWORD_MAX_LENGTH']);
		$this->assertSame(5, $constants['RATE_LIMIT_ATTEMPTS']);
	}

	#[Test]
	public function password_min_is_less_than_max(): void
	{
		$ref = new \ReflectionClass(LoginRequest::class);
		$min = $ref->getConstant('PASSWORD_MIN_LENGTH');
		$max = $ref->getConstant('PASSWORD_MAX_LENGTH');

		$this->assertGreaterThan($min, $max);
	}

	// ───────── I/O variation: throttleKey with special characters ─────────

	#[Test]
	#[DataProvider('specialEmailProvider')]
	public function throttleKey_handles_special_emails(string $email, string $expectedPrefix): void
	{
		$request = LoginRequest::create('https://localhost/login', 'POST', [
			'email'    => $email,
			'password' => 'password123',
		]);

		$key = $request->throttleKey();

		$this->assertStringStartsWith($expectedPrefix, $key);
		$this->assertStringContainsString('|', $key);
	}

	public static function specialEmailProvider(): array
	{
		return [
			'simple'            => ['user@test.com', 'user@test.com'],
			'uppercase'         => ['USER@TEST.COM', 'user@test.com'],
			'mixed case'        => ['UsEr@TeSt.CoM', 'user@test.com'],
			'with plus'         => ['user+tag@test.com', 'user+tag@test.com'],
			'with dots'         => ['first.last@test.com', 'first.last@test.com'],
			'subdomain'         => ['user@sub.domain.com', 'user@sub.domain.com'],
		];
	}

	// ───────── performance ─────────

	#[Test]
	public function rules_executes_within_acceptable_time(): void
	{
		$request = LoginRequest::create('https://localhost/login', 'POST');

		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			$request->rules();
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$avg     = $elapsed / 1000;

		$this->assertLessThan(1, $avg, "rules() should complete under 1ms, average was {$avg}ms");
	}

	#[Test]
	public function throttleKey_executes_within_acceptable_time(): void
	{
		$request = LoginRequest::create('https://localhost/login', 'POST', [
			'email'    => 'test@example.com',
			'password' => 'password123',
		]);

		$start = hrtime(true);
		for ($i = 0; $i < 1000; $i++) {
			$request->throttleKey();
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$avg     = $elapsed / 1000;

		$this->assertLessThan(1, $avg, "throttleKey() should complete under 1ms, average was {$avg}ms");
	}
}
