<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\View\Components\GuestLayout;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class GuestLayoutComponentTest extends TestCase
{
	/**
	 ** @test
	 *
	 ** Ensures the GuestLayout component renders the 'layouts.guest' view
	 ** and logs an info message when render() is called.
	 **/
	public function guest_layout_renders_correct_view_and_logs()
	{
		Log::shouldReceive('info')
			->once()
			->with('Guest layout render called');

		$component = new GuestLayout();
		$view = $component->render();

		$this->assertInstanceOf(View::class, $view);
		$this->assertEquals('layouts.guest', $view->getName());
	}
}
