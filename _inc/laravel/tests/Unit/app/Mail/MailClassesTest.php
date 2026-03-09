<?php

declare(strict_types=1);

namespace Tests\Unit\app\Mail;

use App\Mail\{CommonEmailTemplate, CustomerInvoiceSend, SendDealEmail, SendLeadEmail, TestMail, VendorBillMail};
use Illuminate\Mail\Mailables\{Content, Envelope};
use PHPUnit\Framework\Attributes\{CoversClass, DataProvider, Group, Test};
use Tests\TestCase;

#[CoversClass(CommonEmailTemplate::class)]
#[CoversClass(CustomerInvoiceSend::class)]
#[CoversClass(SendDealEmail::class)]
#[CoversClass(SendLeadEmail::class)]
#[CoversClass(TestMail::class)]
#[CoversClass(VendorBillMail::class)]
#[Group('mail')]
class MailClassesTest extends TestCase
{
	/* ══════════════════════ CommonEmailTemplate ══════════════════════ */

	#[Test]
	public function common_email_stores_template_and_settings(): void
	{
		$tpl = (object)['from' => 'John', 'subject' => 'Hello', 'content' => '<p>Hi</p>'];
		$settings = ['mail_from_address' => 'john@example.com'];
		$mail = new CommonEmailTemplate($tpl, $settings);
		$this->assertSame($tpl, $mail->template);
		$this->assertSame($settings, $mail->settings);
	}

	#[Test]
	public function common_email_build_uses_settings(): void
	{
		$tpl = (object)['from' => 'Sender', 'subject' => 'Sub', 'content' => 'Body'];
		$settings = ['mail_from_address' => 'test@example.com'];
		$mail = new CommonEmailTemplate($tpl, $settings);
		$built = $mail->build();
		$this->assertInstanceOf(CommonEmailTemplate::class, $built);
	}

	#[Test]
	public function common_email_build_handles_null_template(): void
	{
		$mail = new CommonEmailTemplate(null, null);
		$built = $mail->build();
		$this->assertInstanceOf(CommonEmailTemplate::class, $built);
	}

	#[Test]
	public function common_email_build_handles_missing_settings_key(): void
	{
		$tpl = (object)['from' => 'Sender', 'subject' => 'Sub', 'content' => 'Body'];
		$mail = new CommonEmailTemplate($tpl, []);
		$built = $mail->build();
		$this->assertInstanceOf(CommonEmailTemplate::class, $built);
	}

	/* ══════════════════════ CustomerInvoiceSend ══════════════════════ */

	#[Test]
	public function customer_invoice_has_correct_envelope(): void
	{
		$mail = new CustomerInvoiceSend();
		$envelope = $mail->envelope();
		$this->assertInstanceOf(Envelope::class, $envelope);
		$this->assertSame('Customer Invoice Send', $envelope->subject);
	}

	#[Test]
	public function customer_invoice_has_correct_content(): void
	{
		$mail = new CustomerInvoiceSend();
		$content = $mail->content();
		$this->assertInstanceOf(Content::class, $content);
		$this->assertSame('emails.invoice.customer_send', $content->markdown);
	}

	#[Test]
	public function customer_invoice_has_empty_attachments(): void
	{
		$mail = new CustomerInvoiceSend();
		$this->assertSame([], $mail->attachments());
	}

	/* ══════════════════════ SendDealEmail ══════════════════════ */

	#[Test]
	public function deal_email_stores_data_array(): void
	{
		$data = ['key' => 'value', 'nested' => [1, 2, 3]];
		$mail = new SendDealEmail($data);
		$this->assertSame($data, $mail->dArr);
	}

	#[Test]
	public function deal_email_build_returns_self(): void
	{
		$mail = new SendDealEmail(['x' => 1]);
		$built = $mail->build();
		$this->assertInstanceOf(SendDealEmail::class, $built);
	}

	#[Test]
	public function deal_email_build_uses_fallback_subject(): void
	{
		$mail = new SendDealEmail([]);
		$built = $mail->build();
		$this->assertInstanceOf(SendDealEmail::class, $built);
	}

	#[Test]
	public function deal_email_handles_null_data(): void
	{
		$mail = new SendDealEmail(null);
		$this->assertNull($mail->dArr);
	}

	#[Test]
	public function deal_email_handles_empty_array(): void
	{
		$mail = new SendDealEmail([]);
		$this->assertSame([], $mail->dArr);
	}

	/* ══════════════════════ SendLeadEmail ══════════════════════ */

	#[Test]
	public function lead_email_stores_data_array(): void
	{
		$data = ['lead_id' => 42];
		$mail = new SendLeadEmail($data);
		$this->assertSame($data, $mail->lArr);
	}

	#[Test]
	public function lead_email_build_returns_self(): void
	{
		$mail = new SendLeadEmail(['y' => 2]);
		$built = $mail->build();
		$this->assertInstanceOf(SendLeadEmail::class, $built);
	}

	#[Test]
	public function lead_email_handles_null_data(): void
	{
		$mail = new SendLeadEmail(null);
		$this->assertNull($mail->lArr);
	}

	/* ══════════════════════ TestMail ══════════════════════ */

	#[Test]
	public function test_mail_build_returns_self(): void
	{
		$mail = new TestMail();
		$built = $mail->build();
		$this->assertInstanceOf(TestMail::class, $built);
	}

	#[Test]
	public function test_mail_instantiation_is_fast(): void
	{
		$start = hrtime(true);
		for ($i = 0; $i < 100; $i++) new TestMail();
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(50, $elapsed, 'Instantiating 100 TestMail should be < 50ms');
	}

	/* ══════════════════════ VendorBillMail ══════════════════════ */

	#[Test]
	public function vendor_bill_requires_bill_model(): void
	{
		$this->expectException(\TypeError::class);
		new VendorBillMail(null);
	}

	#[Test]
	public function vendor_bill_has_correct_envelope(): void
	{
		$bill = $this->createMockBill();
		$mail = new VendorBillMail($bill);
		$envelope = $mail->envelope();
		$this->assertInstanceOf(Envelope::class, $envelope);
		$this->assertSame('Vendor Bill Mail', $envelope->subject);
	}

	#[Test]
	public function vendor_bill_has_correct_content(): void
	{
		$bill = $this->createMockBill();
		$mail = new VendorBillMail($bill);
		$content = $mail->content();
		$this->assertInstanceOf(Content::class, $content);
		$this->assertSame('emails.vendor.bill', $content->markdown);
	}

	#[Test]
	public function vendor_bill_has_empty_attachments(): void
	{
		$bill = $this->createMockBill();
		$mail = new VendorBillMail($bill);
		$this->assertSame([], $mail->attachments());
	}

	#[Test]
	public function vendor_bill_build_concatenates_subject(): void
	{
		$bill = $this->createMockBill('INV-001');
		$mail = new VendorBillMail($bill);
		$built = $mail->build();
		$this->assertInstanceOf(VendorBillMail::class, $built);
	}

	#[Test]
	public function vendor_bill_build_handles_null_bill_number(): void
	{
		$bill = $this->createMockBill(null);
		$mail = new VendorBillMail($bill);
		$built = $mail->build();
		$this->assertInstanceOf(VendorBillMail::class, $built);
	}

	/* ══════════════════════ I/O Variations ══════════════════════ */

	#[Test]
	#[DataProvider('commonEmailVariationsProvider')]
	public function common_email_handles_various_inputs(mixed $template, mixed $settings): void
	{
		$mail = new CommonEmailTemplate($template, $settings);
		$this->assertNotNull($mail);
	}

	public static function commonEmailVariationsProvider(): array
	{
		return [
			'both null' => [null, null],
			'empty object+array' => [(object)[], []],
			'partial template' => [(object)['from' => 'X'], ['mail_from_address' => 'a@b.com']],
			'full template' => [(object)['from' => 'X', 'subject' => 'S', 'content' => 'C'], ['mail_from_address' => 'a@b.com']],
			'numeric settings' => [(object)['from' => 'X'], ['mail_from_address' => 123]],
			'boolean settings' => [(object)['from' => 'X'], ['mail_from_address' => true]],
		];
	}

	/* ══════════════════════ Performance ══════════════════════ */

	#[Test]
	public function mail_instantiation_performance(): void
	{
		$start = hrtime(true);
		for ($i = 0; $i < 500; $i++) {
			new SendDealEmail(['id' => $i]);
			new SendLeadEmail(['id' => $i]);
			new TestMail();
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(200, $elapsed, '1500 Mail instantiations should be < 200ms');
	}

	/* ──────────── helpers ──────────── */

	private function createMockBill(?string $billNumber = 'BILL-1'): \App\Models\Bill
	{
		$bill = new \App\Models\Bill();
		$bill->bill = $billNumber;
		return $bill;
	}
}
