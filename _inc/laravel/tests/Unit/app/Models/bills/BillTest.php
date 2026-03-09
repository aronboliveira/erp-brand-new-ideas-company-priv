<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use App\Models\{
	Bill,
	Customer,
	Vendor,
	Employee,
	Tax,
	BillAccount,
	BillProduct,
	BillPayment,
	ProductServiceCategory,
	DebitNote
};

class BillTest extends TestCase
{
	protected function setUp(): void
	{
		parent::setUp();
		\DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
	}
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** Bill is mass assignable for all fillable fields
	 **/
	public function bill_is_fillable()
	{
		$customer  = Customer::factory()->create();
		$vendor    = Vendor::factory()->create();
		$employee  = Employee::factory()->create();
		$tax       = Tax::factory()->create();
		$category  = ProductServiceCategory::factory()->create();

		$data = [
			'bill_id'          => 'BILL-1001',
			'vendor_id'        => $vendor->id,
			'currency'         => 'USD',
			'bill_date'        => '2025-05-20',
			'due_date'         => '2025-06-20',
			'order_number'     => 'ORD-789',
			'status'           => 'Unpaid',
			'type'             => 'Standard',
			'user_type'        => 'customer',
			'shipping_display' => 'Flat',
			'send_date'        => '2025-05-21',
			'discount_apply'   => 'yes',
			'category_id'      => $category->id,
			'created_by'       => $employee->id,
		];

		$bill = Bill::create($data);

		$this->assertFillableMatches($data, $bill);
	}

	/**
	 ** @test
	 **
	 ** static statuses array contains expected values
	 **/
	public function statuses_static_property_is_correct()
	{
		$expected = ['Draft', 'Sent', 'Unpaid', 'Partially Paid', 'Paid'];
		$this->assertSame($expected, Bill::$statuses);
	}

	/**
	 ** @test
	 **
	 ** customer() relation should point to Customer via vendor_id
	 **/
	public function customer_relation_resolves_to_customer_model()
	{
		$relation = (new Bill)->customer();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Customer::class,        get_class($relation->getRelated()));
		$this->assertSame('vendor_id',            $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** vendor() relation should point to Vendor via vendor_id
	 **/
	public function vendor_relation_resolves_to_vendor_model()
	{
		$relation = (new Bill)->vendor();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Vendor::class,          get_class($relation->getRelated()));
		$this->assertSame('vendor_id',            $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** employee() relation should point to Employee via vendor_id
	 **/
	public function employee_relation_resolves_to_employee_model()
	{
		$relation = (new Bill)->employee();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Employee::class,        get_class($relation->getRelated()));
		$this->assertSame('vendor_id',            $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** tax() relation should point to Tax via tax_id
	 **/
	public function tax_relation_resolves_to_tax_model()
	{
		$relation = (new Bill)->tax();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Tax::class,             get_class($relation->getRelated()));
		$this->assertSame('tax_id',               $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** accounts() hasMany relation should point to BillAccount via ref_id
	 **/
	public function accounts_relation_resolves_to_bill_account_model()
	{
		$relation = (new Bill)->accounts();

		$this->assertInstanceOf(HasMany::class, $relation);
		$this->assertSame(BillAccount::class,     get_class($relation->getRelated()));
		$this->assertSame('ref_id',               $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** items() hasMany relation should point to BillProduct via bill_id
	 **/
	public function items_relation_resolves_to_bill_product_model()
	{
		$relation = (new Bill)->items();

		$this->assertInstanceOf(HasMany::class, $relation);
		$this->assertSame(BillProduct::class,     get_class($relation->getRelated()));
		$this->assertSame('bill_id',              $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** payments() hasMany relation should point to BillPayment via bill_id
	 **/
	public function payments_relation_resolves_to_bill_payment_model()
	{
		$relation = (new Bill)->payments();

		$this->assertInstanceOf(HasMany::class, $relation);
		$this->assertSame(BillPayment::class,     get_class($relation->getRelated()));
		$this->assertSame('bill_id',              $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** category() relation should point to ProductServiceCategory via category_id
	 **/
	public function category_relation_resolves_to_category_model()
	{
		$relation = (new Bill)->category();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(
			ProductServiceCategory::class,
			get_class($relation->getRelated())
		);
		$this->assertSame('category_id',          $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}

	/**
	 ** @test
	 **
	 ** debitNote() hasMany relation should point to DebitNote via bill
	 **/
	public function debit_note_relation_resolves_to_debit_note_model()
	{
		$relation = (new Bill)->debitNote();

		$this->assertInstanceOf(HasMany::class, $relation);
		$this->assertSame(DebitNote::class,       get_class($relation->getRelated()));
		$this->assertSame('bill',                 $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** lastPayments() hasMany relation should point to BillPayment via id=bill_id
	 **/
	public function last_payments_relation_resolves_correctly()
	{
		$relation = (new Bill)->lastPayments();

		$this->assertInstanceOf(HasMany::class, $relation);
		$this->assertSame(BillPayment::class,     get_class($relation->getRelated()));
		$this->assertSame('id',                   $relation->getForeignKeyName());
		$this->assertSame('bill_id',              $relation->getLocalKeyName());
	}

	/**
	 ** @test
	 **
	 ** getSubTotal returns sum of item and account totals
	 **/
	public function get_subtotal_calculation_is_correct()
	{
		$bill = Bill::factory()->create([
			'items' => [
				['price' => 10.00, 'quantity' => 2, 'discount' => 0, 'tax' => 0],
			],
		]);
		BillAccount::factory()->create([
			'ref_id' => $bill->id,
			'price'  => 5.00,
		]);

		$expected = (10 * 2) + 5.00;
		$this->assertEquals($expected, $bill->getSubTotal());
	}

	/**
	 ** @test
	 **
	 ** getTotalDiscount returns sum of discounts
	 **/
	public function get_total_discount_calculation_is_correct()
	{
		$bill = Bill::factory()->create([
			'items' => [
				['price' => 0, 'quantity' => 1, 'discount' => 3.00, 'tax' => 0],
				['price' => 0, 'quantity' => 1, 'discount' => 2.00, 'tax' => 0],
			],
		]);

		$this->assertEquals(5.00, $bill->getTotalDiscount());
	}

	/**
	 ** @test
	 **
	 ** getTotalTax returns correct sum using Utility::totalTaxRate
	 **/
	public function get_total_tax_calculation_is_correct()
	{
		$bill = Bill::factory()->create([
			'items' => [
				['price' => 20.00, 'quantity' => 1, 'discount' => 2.00, 'tax' => 10],
			],
		]);

		$expectedTax = (10 / 100) * (20 * 1 - 2);
		$this->assertEquals($expectedTax, $bill->getTotalTax());
	}

	/**
	 ** @test
	 **
	 ** getTotal returns subtotal - discount + tax
	 **/
	public function get_total_calculation_is_correct()
	{
		$bill = Bill::factory()->create([
			'items' => [
				['price' => 15.00, 'quantity' => 2, 'discount' => 5.00, 'tax' => 0],
			],
		]);

		$subtotal = 15 * 2;
		$discount = 5.00;
		$tax     = 0.00;
		$this->assertEquals($subtotal - $discount + $tax, $bill->getTotal());
	}

	/**
	 ** @test
	 **
	 ** getDue returns total minus payments and debit notes
	 **/
	public function get_due_calculation_is_correct()
	{
		$bill = Bill::factory()->create([
			'items' => [
				['price' => 30.00, 'quantity' => 1, 'discount' => 0, 'tax' => 0],
			],
		]);
		BillPayment::factory()->create([
			'bill_id' => $bill->id,
			'amount'  => 10.00,
		]);
		DebitNote::factory()->create([
			'bill'   => $bill->id,
			'amount' => 5.00,
		]);

		$this->assertEquals(30.00 - 10.00 - 5.00, $bill->getDue());
	}

	/**
	 ** @test
	 **
	 ** getAccountTotal equals sum of account prices
	 **/
	public function get_account_total_calculation_is_correct()
	{
		$bill = Bill::factory()->create();
		BillAccount::factory()->create([
			'ref_id' => $bill->id,
			'price'  => 7.50,
		]);
		BillAccount::factory()->create([
			'ref_id' => $bill->id,
			'price'  => 2.50,
		]);

		$this->assertEquals(10.00, $bill->getAccountTotal());
	}

	/**
	 ** @test
	 **
	 ** billTotalDebitNote returns sum of debit note amounts
	 **/
	public function bill_total_debit_note_calculation_is_correct()
	{
		$bill = Bill::factory()->create();
		DebitNote::factory()->create([
			'bill'   => $bill->id,
			'amount' => 4.00,
		]);
		DebitNote::factory()->create([
			'bill'   => $bill->id,
			'amount' => 6.00,
		]);

		$this->assertEquals(10.00, $bill->billTotalDebitNote());
	}

	/**
	 ** @test
	 **
	 ** taxes() relation should point to Tax via tax
	 **/
	public function taxes_relation_resolves_to_tax_model()
	{
		$relation = (new Bill)->taxes();

		$this->assertInstanceOf(BelongsTo::class, $relation);
		$this->assertSame(Tax::class,             get_class($relation->getRelated()));
		$this->assertSame('tax',                  $relation->getForeignKeyName());
		$this->assertSame('id',                   $relation->getOwnerKeyName());
	}
}
