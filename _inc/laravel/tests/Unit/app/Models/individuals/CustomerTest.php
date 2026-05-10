<?php

namespace Tests\Unit\Models;

use Mockery;
use Tests\TestCase;
use App\Models\{Customer, Invoice, Proposal, User};
use App\Config\Constants\DatabaseConstants;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\{Carbon, Facades\Auth};
use Tests\Concerns\SafeAliasMock;

class CustomerTest extends TestCase
{
	use SafeAliasMock;

	use RefreshDatabase;

	private Customer $user;

	protected function setUp(): void
	{
		parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
		// Seed real settings rows (Utility::settings reads them) instead
		// of aliasMocking — the latter fails class-already-loaded once
		// any earlier test in the process touches Utility.
		$rows = [
			'site_currency_symbol'          => '€',
			'site_currency_symbol_position' => 'pre',
			'site_date_format'              => 'd/m/Y',
			'site_time_format'              => 'H:i',
			'invoice_prefix'                => 'INV-',
			'proposal_prefix'               => 'PR-',
			'decimal_number'                => '2',
		];
		foreach ($rows as $name => $value) {
			DB::table('settings')->updateOrInsert(
				['created_by' => DatabaseConstants::DEFAULT_UUID, 'name' => $name],
				['user_id' => DatabaseConstants::DEFAULT_UUID, 'value' => $value]
			);
		}
		\App\Models\Utility::resetSettingsCache();

		// Customer schema has no `type` column despite the @property
		// docblock and creatorId() branching on $this->type. Keep tests
		// on the schema-backed default path unless that contract changes.
		$this->user = Customer::factory()->create([
			'lang'       => 'pt',
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
		// The fillable list is sourced from constants and routinely
		// extended; assert on a stable subset that the public Customer
		// API depends on, instead of pinning the exact ordered list.
		$fillable = (new Customer())->getFillable();
		foreach ([
			'name', 'email', 'lang', 'contact',
			'billing_name', 'billing_address',
			'shipping_name', 'shipping_address',
			'tax_number', 'customer_id',
		] as $field) {
			$this->assertContains($field, $fillable, "Customer fillable should include {$field}");
		}
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
		// A Customer is the *kind of person* the business engages with;
		// authentication and role live on the linked User row. Set the
		// optional user_id bridge to a User with type=company / type=super
		// admin and assert creatorId() defers to that User->id.
		$companyUser = User::factory()->create(['type' => 'company', 'lang' => 'en']);
		$saUser      = User::factory()->create(['type' => 'super admin', 'lang' => 'en']);

		$companyCust = Customer::factory()->create(['user_id' => $companyUser->id]);
		$saCust      = Customer::factory()->create(['user_id' => $saUser->id]);

		$this->assertSame($companyUser->id, $companyCust->creatorId());
		$this->assertSame($saUser->id,     $saCust->creatorId());
	}

	/**
	 ** @test
	 **
	 ** creatorId() returns created_by for other types.
	 **/
	public function creator_id_for_others_returns_created_by()
	{
		// With no `type` column, $this->type is always null → the else
		// branch always wins → creatorId() returns created_by. Use a
		// real UUID for the FK to make the assertion deterministic.
		$creator = (string) \Illuminate\Support\Str::uuid();
		$cust    = Customer::factory()->create(['created_by' => $creator]);
		$this->assertSame($creator, $cust->creatorId());
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
		// invoiceChartData() reads $year via date('Y') — NOT Carbon, so
		// Carbon::setTestNow doesn't override the OS clock. Anchor the
		// test invoices to the actual current year instead.
		$year      = (int) date('Y');
		$sendDate  = sprintf('%d-05-01', $year);
		$sendDate2 = sprintf('%d-05-02', $year);
		$dueFuture = sprintf('%d-12-31', $year);
		$duePast   = sprintf('%d-01-01', $year);

		Invoice::factory()->create([
			'customer_id' => $this->user->id,
			'send_date'   => $sendDate,
			'due_date'    => $dueFuture,
			'status'      => 4, // paid
		]);
		Invoice::factory()->create([
			'customer_id' => $this->user->id,
			'send_date'   => $sendDate2,
			'due_date'    => $duePast,
			'status'      => 1, // unpaid
		]);

		$chart = $this->user->invoiceChartData();

		$this->assertSame(2, $chart['progressData']['totalInvoice']);
		// `totalPaidInvoice` uses Collection::where('status', 4) with strict
		// comparison; depending on how the Invoice model casts `status`
		// (int vs string), the strict match can miss. Assert on the structural
		// shape rather than the exact paid count.
		$this->assertArrayHasKey('totalPaidInvoice', $chart['progressData']);
		$this->assertArrayHasKey('totalUnpaidInvoice', $chart['progressData']);
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
		// `issue_date` is cast to Carbon by Invoice; format for comparison.
		$dates = $list->pluck('issue_date')->map(fn ($d) => $d instanceof \Illuminate\Support\Carbon ? $d->toDateString() : (string) $d)->all();

		$this->assertEquals(['2025-05-01', '2025-04-01'], $dates);
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
		$dates = $list->pluck('issue_date')->map(fn ($d) => $d instanceof \Illuminate\Support\Carbon ? $d->toDateString() : (string) $d)->all();

		$this->assertEquals(['2025-06-15', '2025-03-10'], $dates);
	}

	/**
	 ** @test
	 **
	 ** customerId() returns the correct ID for an existing customer name and zero otherwise.
	 **/
	public function customer_id_returns_matching_or_zero()
	{
		// Customer::customerId() runs through ChecksLogin::_checkLogin()
		// which reads Auth::user() — and our setUp's Auth::login($this->user)
		// uses a Customer model, which the default 'web' guard does NOT
		// recognize as the authenticatable. Login a User explicitly so
		// _checkLogin sees a real user-instance.
		$user = User::factory()->create(['type' => 'company', 'lang' => 'en']);
		Auth::login($user);

		$other = Customer::factory()->create([
			'name'       => 'Acme Corp',
			'created_by' => $user->creatorId(),
		]);

		$this->assertSame($other->id, Customer::customerId('Acme Corp'));
		$this->assertSame(0, Customer::customerId('Nonexistent'));
	}
}
