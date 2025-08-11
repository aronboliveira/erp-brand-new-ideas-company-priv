<?php

namespace Tests\Unit\Mail;

use App\Mail\TestMail;
use Illuminate\Mail\Mailable;
use Tests\TestCase;

class TestMailTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** build() must:
	 **  • return `$this`  
	 **  • set the view to `email.test_mail`  
	 **  • set the subject to the expected string  
	 **  • have no additional view data by default
	 **/
	public function build_configures_view_and_subject_correctly(): void
	{
		$mail = new TestMail();

		$returned = $mail->build();

		// build() should return the mailable instance
		$this->assertInstanceOf(
			TestMail::class,
			$returned,
			'build() did not return the mailable instance.'
		);

		// view name must be set
		$this->assertEquals(
			'email.test_mail',
			$mail->view,
			'The view was not set to email.test_mail.'
		);

		// subject must match
		$this->assertEquals(
			'Mail send for testing purpose.',
			$mail->subject,
			'The subject was not set correctly.'
		);

		// no view data should be passed by default
		$this->assertEmpty(
			$mail->viewData,
			'There should be no view data by default.'
		);
	}
}
