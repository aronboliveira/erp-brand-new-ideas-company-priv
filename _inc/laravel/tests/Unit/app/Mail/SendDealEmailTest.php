<?php

namespace Tests\Unit\Mail;

use App\Mail\SendDealEmail;
use Illuminate\Mail\Mailable;
use Tests\TestCase;

class SendDealEmailTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** The constructor must assign the passed `$dArr` data to the public
	 ** property so it’s available for the view.
	 **/
	public function constructor_assigns_dArr(): void
	{
		$data = ['deal' => 42, 'customer' => 'Acme Corp'];
		$mail = new SendDealEmail($data);

		$this->assertSame(
			$data,
			$mail->dArr,
			'Constructor did not assign $dArr correctly.'
		);
	}

	/**
	 ** @test
	 **
	 ** build() must:
	 **  • return `$this` (the mailable instance)  
	 **  • set the view to `email.deal_mail`  
	 **  • pass the `dArr` data to the view under key `dArr`  
	 **  • apply the subject previously set on the mailable
	 **/
	public function build_sets_view_data_and_subject(): void
	{
		$data   = ['amount' => 1000];
		$subject = 'Your Deal Is Ready';

		$mail = new SendDealEmail($data);
		// set the subject before build()
		$mail->subject($subject);

		$returned = $mail->build();

		// build() should return $this
		$this->assertInstanceOf(
			SendDealEmail::class,
			$returned,
			'build() did not return $this.'
		);

		// The view name must be 'email.deal_mail'
		$this->assertEquals(
			'email.deal_mail',
			$mail->view,
			'Unexpected view set on mailable.'
		);

		// The view data must include our $dArr
		$this->assertArrayHasKey(
			'dArr',
			$mail->viewData,
			'dArr key not present in viewData.'
		);
		$this->assertSame(
			$data,
			$mail->viewData['dArr'],
			'dArr was not passed correctly to the view.'
		);

		// Subject must be preserved
		$this->assertEquals(
			$subject,
			$mail->subject,
			'Subject was not applied correctly.'
		);
	}
}
