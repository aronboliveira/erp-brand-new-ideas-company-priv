<?php
// tests/Unit/Mail/SendLeadEmailTest.php

namespace Tests\Unit\Mail;

use App\Mail\SendLeadEmail;
use Illuminate\Mail\Mailable;
use Tests\TestCase;

class SendLeadEmailTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** The constructor should assign the provided `$lArr`
	 ** to the public property.
	 **/
	public function constructor_assigns_lArr(): void
	{
		$data = ['lead' => 'Acme Corp.', 'email' => 'lead@acme.test'];

		$mailable = new SendLeadEmail($data);

		$this->assertSame($data, $mailable->lArr);
	}

	/**
	 ** @test
	 **
	 ** The `build()` method should:
	 **   • use the `email.lead_mail` view  
	 **   • pass the `lArr` variable into the view data  
	 **   • set the subject from `$this->subject` (default null).
	 **/
	public function build_configures_view_data_and_subject(): void
	{
		$data    = ['foo' => 'bar'];
		$mailable = new SendLeadEmail($data);

		/** @var Mailable $built */
		$built = $mailable->build();

		// View used
		$this->assertSame('email.lead_mail', $built->view);

		// View data includes lArr
		$this->assertArrayHasKey('lArr', $built->viewData);
		$this->assertSame($data, $built->viewData['lArr']);

		// Subject matches the mailable's subject property (null by default)
		$this->assertNull($built->subject);
	}
}
