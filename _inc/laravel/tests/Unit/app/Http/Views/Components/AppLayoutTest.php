<?php

namespace Tests\Unit\View\Components;

use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
use App\View\Components\AppLayout;

class AppLayoutTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** The render method should write an info log and return the 'layouts.app' view.
	 **/
	public function render_logs_and_returns_layouts_app()
	{
		// Expect the Log facade to receive exactly one 'info' call with our message
		Log::shouldReceive('info')
			->once()
			->with('Guest layout render called');

		$component = new AppLayout();
		$view = $component->render();

		// Assert we got a View instance and it's the correct view name
		$this->assertInstanceOf(View::class, $view);
		$this->assertEquals('layouts.app', $view->getName());
	}
}
