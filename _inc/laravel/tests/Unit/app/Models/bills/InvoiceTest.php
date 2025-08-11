<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\{HasOne, HasMany};
use App\Models\{
	Invoice,
	Tax,
	InvoiceProduct,
	InvoicePayment,
	InvoiceBankTransfer,
	Customer,
	ProductServiceCategory,
	CreditNote
};

class InvoiceTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Invoice is mass assignable for all fillable fields
	 **/
	public function invoice_is_fillable()
	{
		$customer = Customer::factory()->create();
		$tax     = Tax::factory()->create();
		$category = ProductServiceCategory::factory()->create();

		$data = [
			'invoice_id'        => 'INV-2025-01',
			'customer_id'       => $customer->id,
			'issue_date'        => '2025-05-01',
			'due_date'          => '2025-05-31',
			'send_date'         => '2025-05-02',
			'ref_number'        => 'REF-100',
			'status'            => 'Sent',
			'shipping_display'  => 'Express',
			'discount_apply'    => 'yes',
			'category_id'       => $category->id,
			'tax_id'            => $tax->id,
			'created_by'        => 'user-xyz',
		];

		$invoice = Invoice::create($data);

		foreach ($data as $field => $value) {
			$this->assertEquals($value, $invoice->$field);
		}
	}

	/**
	 ** @test
	 **
	 ** Invoice uses UUIDs for its primary key
	 **/
	public function invoice_uses_uuid_for_primary_key()
	{
		$invoice = Invoice::factory()->create();
		$key    = $invoice->getKey();

		$this->assertIsString($key);
		$this->assertFalse($invoice->getIncrementing());
		$this->assertSame('string', $invoice->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** static statuses array contains expected values
	 **/
	public function statuses_static_property_is_correct()
	{
		$expected = ['Draft', 'Sent', 'Unpaid', 'Partially Paid', 'Paid'];
		$this->assertSame($expected, Invoice::$statuses);
	}

	/**
	 ** @test
	 **
	 ** tax() relation should point to Tax via tax_id
	 **/
	public function tax_relation_resolves_to_tax_model()
	{
		$relation = (new Invoice)->tax();
		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(Tax::class,          get_class($relation->getRelated()));
		$this->assertSame('id',                $relation->getForeignKeyName());
		$this->assertSame('tax_id',            $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** taxes() relation should point to Tax via tax
	 **/
	public function taxes_relation_resolves_to_tax_model()
	{
		$relation = (new Invoice)->taxes();
		$this->assertInstanceOf(HasOne::class, $relation);
		$this->assertSame(Tax::class,          get_class($relation->getRelated()));
		$this->assertSame('id',                $relation->getForeignKeyName());
		$this->assertSame('tax',               $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** items() and products() relations should point to InvoiceProduct via invoice_id
	 **/
	public function items_and_products_relations_resolve_to_invoice_product_model()
	{
		$itemsRel   = (new Invoice)->items();
		$productsRel = (new Invoice)->products();

		foreach ([$itemsRel, $productsRel] as $relation) {
			$this->assertInstanceOf(HasMany::class,      $relation);
			$this->assertSame(InvoiceProduct::class,     get_class($relation->getRelated()));
			$this->assertSame('invoice_id',              $relation->getForeignKeyName());
			$this->assertSame('id',                      $relation->getLocalKeyName());
		}
	}

	/**
	 ** @test
	 **
	 ** payments() relation should point to InvoicePayment via invoice_id
	 **/
	public function payments_relation_resolves_to_invoice_payment_model()
	{
		$relation = (new Invoice)->payments();
		$this->assertInstanceOf(HasMany::class,    $relation);
		$this->assertSame(InvoicePayment::class,   get_class($relation->getRelated()));
		$this->assertSame('invoice_id',            $relation->getForeignKeyName());
		$this->assertSame('id',                    $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** lastPayments() relation should point to InvoicePayment via id=invoice_id
	 **/
	public function last_payments_relation_resolves_correctly()
	{
		$relation = (new Invoice)->lastPayments();
		$this->assertInstanceOf(HasOne::class,     $relation);
		$this->assertSame(InvoicePayment::class,   get_class($relation->getRelated()));
		$this->assertSame('id',                    $relation->getForeignKeyName());
		$this->assertSame('invoice_id',            $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** bankPayments() relation applies a where status != Approved
	 **/
	public function bank_payments_relation_includes_status_filter()
	{
		$relation = (new Invoice)->bankPayments();
		$this->assertInstanceOf(HasMany::class,           $relation);
		$this->assertSame(InvoiceBankTransfer::class,     get_class($relation->getRelated()));
		$this->assertSame('invoice_id',                   $relation->getForeignKeyName());
		$this->assertSame('id',                           $relation->getLocalKeyName());

		$wheres = $relation->getQuery()->wheres;
		$this->assertTrue(collect($wheres)->contains(function ($w) {
			return $w['column'] === 'status'
				&& $w['operator'] === '!='
				&& $w['value'] === 'Approved';
		}));
	}

	/**
	 ** @test
	 **
	 ** customer() and category() relations resolve correctly
	 **/
	public function customer_and_category_relations_resolve_correctly()
	{
		$custRel = (new Invoice)->customer();
		$catRel = (new Invoice)->category();

		$this->assertInstanceOf(HasOne::class,        $custRel);
		$this->assertSame(Customer::class,            get_class($custRel->getRelated()));
		$this->assertSame('id',                       $custRel->getForeignKeyName());
		$this->assertSame('customer_id',              $custRel->getLocalKeyName());

		$this->assertInstanceOf(HasOne::class,        $catRel);
		$this->assertSame(ProductServiceCategory::class, get_class($catRel->getRelated()));
		$this->assertSame('id',                       $catRel->getForeignKeyName());
		$this->assertSame('category_id',              $catRel->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** invoiceTotalCreditNote() sums related CreditNote amounts
	 **/
	public function invoice_total_credit_note_calculation_is_correct()
	{
		$invoice = Invoice::factory()->create();
		CreditNote::factory()->count(2)->create([
			'invoice'    => $invoice->id,
			'customer'   => $invoice->customer_id,
			'amount'     => 25.00,
			'date'       => Carbon::now()->toDateString(),
			'description' => 'Refund'
		]);

		$this->assertEquals(50.00, $invoice->invoiceTotalCreditNote());
	}

	/**
	 ** @test
	 **
	 ** getSubTotal, getTotalDiscount, getTotalTax, getTotal, and getDue calculations are correct
	 **/
	public function financial_calculations_are_correct()
	{
		$invoice = Invoice::factory()->create();
		// Two items: (price * qty) = 10*2 + 5*3 = 35
		InvoiceProduct::factory()->create([
			'invoice_id' => $invoice->id,
			'price'     => 10.00,
			'quantity'  => 2,
			'discount'  => 1.00,
			'tax'       => 0,
		]);
		InvoiceProduct::factory()->create([
			'invoice_id' => $invoice->id,
			'price'     => 5.00,
			'quantity'  => 3,
			'discount'  => 0.50,
			'tax'       => 0,
		]);

		$subtotal      = 10 * 2 + 5 * 3; // 35
		$totalDiscount = 1.00 + 0.50; // 1.50
		$totalTax      = 0.0;
		$total         = ($subtotal - $totalDiscount) + $totalTax; // 33.5

		$this->assertEquals($subtotal,      $invoice->getSubTotal());
		$this->assertEquals($totalDiscount, $invoice->getTotalDiscount());
		$this->assertEquals($totalTax,      $invoice->getTotalTax());
		$this->assertEquals($total,         $invoice->getTotal());

		// Add a payment of 10 and a credit note of 5 => due = total - 10 - 5 = 18.5
		InvoicePayment::factory()->create([
			'invoice_id' => $invoice->id,
			'amount'    => 10.00,
		]);
		CreditNote::factory()->create([
			'invoice'    => $invoice->id,
			'customer'   => $invoice->customer_id,
			'amount'     => 5.00,
			'date'       => Carbon::now()->toDateString(),
			'description' => 'Partial refund'
		]);

		$this->assertEquals(18.50, $invoice->getDue());
	}

	/**
	 ** @test
	 **
	 ** changeStatus updates the invoice status
	 **/
	public function change_status_updates_invoice_status()
	{
		$invoice = Invoice::factory()->create(['status' => 'Draft']);
		Invoice::changeStatus($invoice->id, 'Paid');
		$this->assertSame('Paid', $invoice->fresh()->status);
	}
}
