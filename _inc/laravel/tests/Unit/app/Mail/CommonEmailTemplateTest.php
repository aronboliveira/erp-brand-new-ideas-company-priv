<?php
// tests/Unit/Mail/CommonEmailTemplateTest.php

namespace Tests\Unit\Mail;

use App\Mail\CommonEmailTemplate;
use Illuminate\Mail\Mailable;
use Tests\TestCase;

class CommonEmailTemplateTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** The constructor should assign the provided `$template`
	 ** and `$settings` to the public properties.
	 **/
	public function constructor_assigns_template_and_settings(): void
	{
		$template = (object)[
			'from'    => 'AcmeApp',
			'subject' => 'Welcome!',
			'content' => 'Hello, world!',
		];
		$settings = ['mail_from_address' => 'noreply@acme.test'];

		$mailable = new CommonEmailTemplate($template, $settings);

		$this->assertSame($template, $mailable->template);
		$this->assertSame($settings, $mailable->settings);
	}

	/**
	 ** @test
	 **
	 ** The `build()` method should:
	 **   • set the email subject from `$template->subject`  
	 **   • set the "from" address and name using `$settings['mail_from_address']`
	 **     and `$template->from`  
	 **   • use the `email.common_email_template` markdown view  
	 **   • pass the `content` variable into the view data.
	 **/
	public function build_configures_message_correctly(): void
	{
		$template = (object)[
			'from'    => 'AcmeApp',
			'subject' => 'Greetings!',
			'content' => 'This is the body.',
		];
		$settings = ['mail_from_address' => 'no-reply@acme.test'];

		$mailable = new CommonEmailTemplate($template, $settings);
		/** @var Mailable $built */
		$built = $mailable->build();

		// Subject
		$this->assertSame('Greetings!', $built->subject);

		// From address and name
		$from = $built->from;
		$this->assertCount(1, $from);
		$this->assertSame('no-reply@acme.test', $from[0]['address']);
		$this->assertSame('AcmeApp', $from[0]['name']);

		// View used (markdown sets $this->markdown, not $this->view)
		$this->assertSame('email.common_email_template', $built->markdown);

		// View Data
		$data = $built->viewData;
		$this->assertArrayHasKey('content', $data);
		$this->assertSame('This is the body.', $data['content']);
	}

	/**
	 ** @test
	 **
	 ** If the `from()` call within `build()` throws an exception,
	 ** it should propagate the exception.
	 **/
	public function build_propagates_exception_from_from_method(): void
	{
		$template = (object)[
			'from'    => 'AcmeApp',
			'subject' => 'Test',
			'content' => 'Body',
		];
		$settings = ['mail_from_address' => 'no-reply@acme.test'];

		// Create a partial mock where `from()` throws
		/** @var CommonEmailTemplate $stub */
		$stub = $this->createPartialMock(
			CommonEmailTemplate::class,
			['from'],
			[$template, $settings]
		);
		$stub->expects($this->once())
			->method('from')
			->willThrowException(new \Exception('fail-build'));

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('fail-build');

		$stub->build();
	}
}
