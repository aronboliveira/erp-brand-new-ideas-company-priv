<?php
// tests/Unit/Mail/VendorBillMailTest.php

namespace Tests\Unit\Mail;

use App\Mail\VendorBillMail;
use App\Models\Bill;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Tests\TestCase;

class VendorBillMailTest extends TestCase
{
	/**
	 ** @test
	 **
	 ** The constructor should assign the provided `Bill`
	 ** instance to the public `$bill` property.
	 **/
	public function constructor_assigns_bill(): void
	{
		$bill = new Bill();
		$bill->bill = 'INV-123';

		$mailable = new VendorBillMail($bill);

		$this->assertSame($bill, $mailable->bill);
	}

	/**
	 ** @test
	 **
	 ** The `envelope()` method should return an `Envelope`
	 ** with the subject "Vendor Bill Mail".
	 **/
	public function envelope_returns_expected_subject(): void
	{
		$mail = new VendorBillMail(new Bill());
		$envelope = $mail->envelope();

		$this->assertInstanceOf(Envelope::class, $envelope);
		$this->assertSame('Vendor Bill Mail', $envelope->subject);
	}

	/**
	 ** @test
	 **
	 ** The `content()` method should return a `Content`
	 ** pointing to the `emails.vendor.bill` markdown view.
	 **/
	public function content_returns_expected_markdown_view(): void
	{
		$mail = new VendorBillMail(new Bill());
		$content = $mail->content();

		$this->assertInstanceOf(Content::class, $content);
		$this->assertSame('emails.vendor.bill', $content->markdown);
	}

	/**
	 ** @test
	 **
	 ** The `attachments()` method should return an empty array.
	 **/
	public function attachments_return_empty_array(): void
	{
		$mail = new VendorBillMail(new Bill());
		$this->assertSame([], $mail->attachments());
	}

	/**
	 ** @test
	 **
	 ** The `build()` method should:
	 **   • set the subject to "Your Invoice #<bill>"  
	 **   • use the `emails.vendor.bill` markdown view  
	 **   • pass the `bill` variable into the view data.
	 **/
	public function build_configures_subject_view_and_data(): void
	{
		$bill = new Bill();
		$bill->bill = 'XYZ-789';

		$mail   = new VendorBillMail($bill);
		/** @var Mailable $built */
		$built = $mail->build();

		// Subject should be translated "Your Invoice" plus " #XYZ-789"
		$this->assertSame('Your Invoice #XYZ-789', $built->subject);

		// Markdown view used
		$this->assertSame('emails.vendor.bill', $built->markdown);

		// View data should contain the bill instance
		$this->assertArrayHasKey('bill', $built->viewData);
		$this->assertSame($bill, $built->viewData['bill']);
	}
}
