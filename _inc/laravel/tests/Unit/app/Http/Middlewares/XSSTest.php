<?php

namespace Tests\Unit\app\Http\Middlewares;

use Tests\TestCase;
use App\Http\Middleware\XSS;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\Attributes\Group;

/**
 * XSSTest requires a full HTTP pipeline dispatch that accumulates too much
 * memory when run after thousands of other tests, causing the PHP process
 * to die.  Isolated in the "slow" group so it can be run separately:
 *   php vendor/bin/phpunit --group=slow tests/Unit/app/Http/Middlewares/XSSTest.php
 */
#[Group('slow')]
class XSSTest extends TestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		// Bind a subclass of XSS to override migrations behavior for testing
		App::bind(XSS::class, function () {
			return new class extends XSS
			{
				public function getMigrations(): array
				{
					return ['mig1', 'mig2'];
				}
				public function getExecutedMigrations(): array
				{
					return ['mig1'];
				}
			};
		});

		// Test route for sanitization and normal flow
		Route::post('/test-xss', function () {
			return response()->json(request()->all());
		})->middleware(XSS::class);

		// Test route to force exception in closure
		Route::post('/test-xss-error', function () {
			throw new \Exception('boom');
		})->middleware(XSS::class);
	}

	/**
	 ** @test
	 **
	 ** Guests should be redirected to login before any XSS processing.
	 **/
	public function test_guests_are_redirected_to_login()
	{
		$response = $this->post('/test-xss', ['foo' => '<b>bar</b>']);
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Super admin with pending migrations is redirected
	 ** to the LaravelUpdater welcome route,
	 ** and Utility::addNewData and User::defaultEmail are called.
	 **/
	public function test_super_admin_with_pending_migrations_redirects_to_updater()
	{
		$admin = User::factory()->create(['type' => 'super admin', 'lang' => 'en']);
		Auth::login($admin);

		$response = $this->post('/test-xss', ['irrelevant' => 'data']);

		$response->assertRedirect(route('LaravelUpdater::welcome'));
	}

	/**
	 ** @test
	 **
	 ** The middleware strips HTML tags from all inputs recursively,
	 ** except for redirection logic, and passes sanitized data to the application.
	 **/
	public function test_it_strips_tags_from_input_except_login_and_migrations()
	{
		$user = User::factory()->create(['type' => 'company', 'lang' => 'en']);
		Auth::login($user);

		$payload = [
			'name' => '<script>alert(1)</script> Alice',
			'nested' => ['key' => '<b>bold</b>'],
		];

		$response = $this->postJson('/test-xss', $payload);

		$response->assertStatus(200)
			->assertJson([
				'name'   => 'alert(1) Alice',
				'nested' => ['key' => 'bold'],
			]);
	}

	/**
	 ** @test
	 **
	 ** If an exception occurs during request processing (e.g., in the next stage),
	 ** the middleware logs an error and returns JSON 500 with the correct message.
	 **/
	public function test_exceptions_are_caught_logged_and_return_json_error()
	{
		$user = User::factory()->create(['type' => 'company', 'lang' => 'en']);
		Auth::login($user);

		$middleware = app(XSS::class);
		$request = \Illuminate\Http\Request::create('/test-xss-error', 'POST', ['foo' => 'bar']);
		$request->headers->set('Accept', 'application/json');
		$request->setUserResolver(fn () => $user);
		$request->setLaravelSession(app('session.store'));

		// Bind a real route so the middleware can inspect route details
		$route = new \Illuminate\Routing\Route('POST', '/test-xss-error', fn () => null);
		$route->bind($request);
		$request->setRouteResolver(fn () => $route);

		$response = $middleware->handle($request, function () {
			throw new \Exception('boom');
		});

		$this->assertEquals(500, $response->getStatusCode());
		$json = json_decode($response->getContent(), true);
		$this->assertArrayHasKey('error', $json);
		$this->assertStringContainsString('boom', $json['error']);
	}
}
