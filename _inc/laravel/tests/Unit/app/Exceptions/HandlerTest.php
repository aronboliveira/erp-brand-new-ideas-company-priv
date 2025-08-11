<?php

namespace Tests\Unit\Exceptions;

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Exceptions\Handler;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class HandlerTest extends TestCase
{
	use MockeryPHPUnitIntegration;

	/**
	 ** @test
	 **
	 ** register() should add a reportable callback that logs the exception details.
	 **/
	public function register_adds_reportable_callback()
	{
		// Arrange: resolve the handler and clear existing callbacks
		/** @var Handler $handler */
		$handler = $this->app->make(Handler::class);
		$reflection = new ReflectionClass(Handler::class);
		$prop = $reflection->getParentClass()  // ExceptionHandler
			->getProperty('reportCallbacks');
		$prop->setAccessible(true);
		$prop->setValue($handler, []);

		// Expect Log::error with the correct signature
		$exception = new \RuntimeException('failure', 123);
		Log::shouldReceive('error')
			->once()
			->with(
				Handler::class . '::register reportable triggered',
				Mockery::on(function ($context) use ($exception) {
					return $context['exception'] === get_class($exception)
						&& $context['message'] === $exception->getMessage()
						&& $context['file'] === $exception->getFile()
						&& $context['line'] === $exception->getLine()
						&& $context['code'] === $exception->getCode();
				})
			);

		// Act: register and retrieve the callback
		$handler->register();
		$callbacks = $prop->getValue($handler);
		$this->assertCount(1, $callbacks);
		$this->assertIsCallable($callbacks[0]);

		// Invoke the reportable callback
		/** @var callable $callback */
		$callback = $callbacks[0];
		$callback($exception);
	}

	/**
	 ** @test
	 **
	 ** render() should return a Symfony Response for a normal exception.
	 **/
	public function render_returns_symfony_response_for_exception()
	{
		$handler = $this->app->make(Handler::class);
		$request = Request::create('/test', 'GET');
		$exception = new \Exception('oops');

		$response = $handler->render($request, $exception);

		$this->assertInstanceOf(Response::class, $response);
		$this->assertEquals(500, $response->getStatusCode());
	}
}
