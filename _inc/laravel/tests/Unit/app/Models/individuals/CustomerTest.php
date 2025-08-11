<?php

namespace Tests\Unit\Models;

use Mockery;
use Tests\TestCase;
use App\Models\{Customer, Invoice, Proposal, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\{Carbon, Facades\Auth};

class CustomerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();
		// stub Utility::settings and Utility::getValByName
		Mockery::mock('alias:App\Models\Utility')
			->shouldReceive('settings')->andReturn([
				'site_currency_symbol'           => '€',
				'site_currency_symbol_position'  => 'pre',
				'site_date_format'               => 'd/m/Y',
				'site_time_format'               => 'H:i',
				'invoice_prefix'                 => 'INV-',
				'proposal_prefix'                => 'PR-',
			])->byDefault();
		Mockery::mock('alias:App\Models\Utility')
			->shouldReceive('getValByName')->with('decimal_number')->andReturn(2)
			->byDefault();

		// create and authenticate a user
		$this->user = Customer::factory()->create([
			'lang'       => 'pt',
			'type'       => 'employee',
			'created_by' => null,
		]);
		Auth::login($this->user);
	}

	/**
	 ** @test
	 **
	 ** The Customer model has the expected fillable fields.
	 **/
	public function it_has_expected_fillable_fields()
	{
		$expected = [
			'billing_address', 'billing_city', 'billing_country', 'billing_name',
			'billing_phone', 'billing_state', 'billing_zip', 'contact',
			'created_by', 'email', 'email_verified_at', 'avatar', 'is_active',
			'lang', 'name', 'password', 'proposal_prefix', 'shipping_address',
			'shipping_city', 'shipping_country', 'shipping_name', 'shipping_phone',
			'shipping_state', 'shipping_zip', 'tax_number', 'customer_id',
		];
		$this->assertEquals($expected, (new Customer())->getFillable());
	}

	/**
	 ** @test
	 **
	 ** authId() returns the model's id.
	 **/
	public function auth_id_returns_id()
	{
		$this->assertSame($this->user->id, $this->user->authId());
	}

	/**
	 ** @test
	 **
	 ** creatorId() returns id when type is company or super admin.
	 **/
	public function creator_id_for_company_or_super_admin_is_self_id()
	{
		$company = Customer::factory()->create(['type' => 'company', 'created_by' => 123]);
		$super  = Customer::factory()->create(['type' => 'super admin', 'created_by' => 456]);
		$this->assertSame($company->id, $company->creatorId());
		$this->assertSame($super->id,   $super->creatorId());
	}

	/**
	 ** @test
	 **
	 ** creatorId() returns created_by for other types.
	 **/
	public function creator_id_for_others_returns_created_by()
	{
		$cust = Customer::factory()->create(['type' => 'employee', 'created_by' => 789]);
		$this->assertSame(789, $cust->creatorId());
	}

	/**
	 ** @test
	 **
	 ** currentLanguage() returns the lang attribute.
	 **/
	public function current_language_returns_lang()
	{
		$this->user->lang = 'es';
		$this->assertSame('es', $this->user->currentLanguage());
	}

	/**
	 ** @test
	 **
	 ** dateFormat() uses the configured date format.
	 **/
	public function date_format_applies_setting()
	{
		$formatted = $this->user->dateFormat('2025-05-29');
		$this->assertSame('29/05/2025', $formatted);
	}

	/**
	 ** @test
	 **
	 ** timeFormat() uses the configured time format.
	 **/
	public function time_format_applies_setting()
	{
		$formatted = $this->user->timeFormat('14:35:00');
		$this->assertSame('14:35', $formatted);
	}

	/**
	 ** @test
	 **
	 ** invoiceNumberFormat() prefixes and pads the number.
	 **/
	public function invoice_number_format_prefixes_and_pads()
	{
		$this->assertSame('INV-00042', $this->user->invoiceNumberFormat(42));
	}

	/**
	 ** @test
	 **
	 ** proposalNumberFormat() prefixes and pads the number.
	 **/
	public function proposal_number_format_prefixes_and_pads()
	{
		$this->assertSame('PR-00123', $this->user->proposalNumberFormat(123));
	}

	/**
	 ** @test
	 **
	 ** priceFormat() applies currency symbol and decimal places.
	 **/
	public function price_format_applies_currency_symbol_and_decimals()
	{
		$price = $this->user->priceFormat(1234.5);
		$this->assertSame('€1,234.50', $price);
	}

	/**
	 ** @test
	 **
	 ** currencySymbol() returns the configured symbol.
	 **/
	public function currency_symbol_returns_setting()
	{
		$this->assertSame('€', $this->user->currencySymbol());
	}

	/**
	 ** @test
	 **
	 ** invoiceChartData() calculates correct invoice counts for paid and unpaid.
	 **/
	public function invoice_chart_data_counts_paid_and_unpaid()
	{
		Carbon::setTestNow(Carbon::create(2025, 5, 29));

		// a paid invoice (status 4) and an unpaid invoice (status 1)
		Invoice::factory()->create([
			'customer_id' => $this->user->id,
			'send_date'   => '2025-05-01',
			'due_date'    => '2025-06-01',
			'status'      => 4,
		]);
		Invoice::factory()->create([
			'customer_id' => $this->user->id,
			'send_date'   => '2025-05-02',
			'due_date'    => '2025-05-15',
			'status'      => 1,
		]);

		$chart = $this->user->invoiceChartData();

		$this->assertSame(2, $chart['progressData']['totalInvoice']);
		$this->assertSame(1, $chart['progressData']['totalPaidInvoice']);
		$this->assertSame(1, $chart['progressData']['totalUnpaidInvoice']);
	}

	/**
	 ** @test
	 **
	 ** customerInvoice() returns invoices ordered by issue_date descending.
	 **/
	public function customer_invoice_returns_ordered_by_issue_date()
	{
		Invoice::factory()->create([
			'customer_id' => $this->user->id,
			'issue_date'  => '2025-04-01',
		]);
		Invoice::factory()->create([
			'customer_id' => $this->user->id,
			'issue_date'  => '2025-05-01',
		]);

		$list = $this->user->customerInvoice($this->user->id);

		$this->assertEquals(
			['2025-05-01', '2025-04-01'],
			$list->pluck('issue_date')->all()
		);
	}

	/**
	 ** @test
	 **
	 ** customerProposal() returns proposals ordered by issue_date descending.
	 **/
	public function customer_proposal_returns_ordered_by_issue_date()
	{
		Proposal::factory()->create([
			'customer_id' => $this->user->id,
			'issue_date'  => '2025-03-10',
		]);
		Proposal::factory()->create([
			'customer_id' => $this->user->id,
			'issue_date'  => '2025-06-15',
		]);

		$list = $this->user->customerProposal($this->user->id);

		$this->assertEquals(
			['2025-06-15', '2025-03-10'],
			$list->pluck('issue_date')->all()
		);
	}

	/**
	 ** @test
	 **
	 ** customerId() returns the correct ID for an existing customer name and zero otherwise.
	 **/
	public function customer_id_returns_matching_or_zero()
	{
		$other = Customer::factory()->create([
			'name'       => 'Acme Corp',
			'created_by' => $this->user->creatorId(),
		]);

		$this->assertSame($other->id, Customer::customerId('Acme Corp'));
		$this->assertSame(0, Customer::customerId('Nonexistent'));
	}
}
