<?php

namespace Tests\Unit\Models;

use App\Models\{
	Bill,
	Branch,
	Customer,
	Deal,
	Department,
	Designation,
	EmailTemplate,
	EmailTemplateLang,
	Employee,
	Invoice,
	Lead,
	LeaveType,
	Location,
	Order,
	Payment,
	Plan,
	Project,
	ProjectTask,
	ProjectUser,
	Revenue,
	TaskStage,
	User,
	UserContact,
	UserEmailTemplate,
	UserToDo,
	Vendor
};
use Illuminate\Database\Eloquent\Relations\{
	BelongsToMany,
	HasMany,
	HasOne
};
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Http\UploadedFile,
	Support\Carbon
};
use Illuminate\Support\Facades\{Auth, DB, Storage};
use Tests\TestCase;

class Helper
{
	public static function storeSetting(string $key, $value): void
	{
		\App\Models\Utility::settings()[$key] = $value;
	}
	public static function clearSetting(string $key): void
	{
		\App\Models\Utility::settings()[$key] = null;
	}
}

class UserBasicTest extends TestCase
{
	use RefreshDatabase;

	protected function setUp(): void
	{
		parent::setUp();
		Carbon::setTestNow('2025-05-29 12:00:00');
		DB::table('settings')->insert([
			['name' => 'site_currency_symbol',              'value' => '$',    'created_by' => 1],
			['name' => 'site_currency_symbol_position',     'value' => 'pre',  'created_by' => 1],
			['name' => 'site_date_format',                  'value' => 'd/m/Y', 'created_by' => 1],
			['name' => 'site_time_format',                  'value' => 'H:i',  'created_by' => 1],
			['name' => 'purchase_prefix',                   'value' => 'PU-',  'created_by' => 1],
			['name' => 'pos_prefix',                        'value' => 'POS-', 'created_by' => 1],
			['name' => 'invoice_prefix',                    'value' => 'INV-', 'created_by' => 1],
			['name' => 'proposal_prefix',                   'value' => 'PR-',  'created_by' => 1],
			['name' => 'contract_prefix',                   'value' => 'C-',   'created_by' => 1],
			['name' => 'bill_prefix',                       'value' => 'B-',   'created_by' => 1],
			['name' => 'expense_prefix',                    'value' => 'E-',   'created_by' => 1],
			['name' => 'journal_prefix',                    'value' => 'J-',   'created_by' => 1],
			['name' => 'employee_prefix',                   'value' => 'EMP-', 'created_by' => 1],
			// decimal places for currency
			['name' => 'decimal_number',                    'value' => '2',    'created_by' => 1],
			['name' => 'employee_prefix',   'value' => 'EMP-',   'created_by' => 1],
			['name' => 'customer_prefix',   'value' => 'CUST-',  'created_by' => 1],
			['name' => 'vendor_prefix',     'value' => 'VEND-',  'created_by' => 1],
			['name' => 'bug_prefix',        'value' => 'BUG-',   'created_by' => 1],
			['name' => 'barcode_format',    'value' => 'code39', 'created_by' => 1],
			['name' => 'barcode_type',      'value' => 'svg',    'created_by' => 1],
		]);
	}

	/**
	 ** @test
	 **
	 ** The model has the expected fillable, hidden, casts, and appends properties.
	 **/
	public function it_has_expected_model_configuration()
	{
		$user = new User;

		$this->assertEquals([
			'name', 'email', 'password', 'type', 'storage_limit', 'avatar',
			'lang', 'mode', 'delete_status', 'plan', 'email_verified_at',
			'plan_expire_date', 'requested_plan', 'is_active', 'last_login_at',
			'created_by'
		], $user?->getFillable());

		$this->assertEquals(['password', 'remember_token'], $user?->getHidden());
		$this->assertEquals(['email_verified_at' => 'datetime'], $user?->getCasts());
		$this->assertEquals(['profile'], $user?->getAppends());
	}

	/**
	 ** @test
	 **
	 ** getProfileAttribute returns full URL when avatar exists, fallback otherwise.
	 **/
	public function get_profile_attribute_uses_storage_url_or_fallback()
	{
		Storage::shouldReceive('exists')
			->once()
			->with('avatars/john.png')
			->andReturnTrue();
		Storage::shouldReceive('url')
			->once()
			->with('avatars/john.png')
			->andReturn('avatars/john.png');

		$user = User::factory()->make(['avatar' => 'avatars/john.png']);
		$this->assertStringContainsString('avatars/john.png', $user?->profile);

		Storage::shouldReceive('exists')
			->once()
			->with('')
			->andReturnFalse();
		Storage::shouldReceive('url')
			->once()
			->with('avatar.png')
			->andReturn('avatar.png');

		$user2 = User::factory()->make(['avatar' => '']);
		$this->assertStringContainsString('avatar.png', $user2->profile);
	}

	/**
	 ** @test
	 **
	 ** authId(), creatorId(), ownerId(), ownerDetails(), and currentLanguage() behave correctly.
	 **/
	public function it_returns_correct_ids_and_language()
	{
		// company user: creatorId and ownerId are same as id
		$company = User::factory()->create(['type' => 'company']);
		$this->assertEquals($company->id, $company->authId());
		$this->assertEquals($company->id, $company->creatorId());
		$this->assertEquals($company->id, $company->ownerId());
		$this->assertEquals($company->id, $company->ownerDetails()->id);

		// standard user: creatorId and ownerId from created_by
		$owner = User::factory()->create(['type' => 'company']);
		$user = User::factory()->create(['type' => 'user', 'created_by' => $owner->id]);
		$this->assertEquals($owner->id, $user?->creatorId());
		$this->assertEquals($owner->id, $user?->ownerId());
		$this->assertEquals($owner->id, $user?->ownerDetails()->id);

		// language
		$langUser = User::factory()->make(['lang' => 'es']);
		$this->assertSame('es', $langUser->currentLanguage());
	}

	/**
	 ** @test
	 **
	 ** countUsers() counts non-admin, non-company, non-client users created by this user.
	 **/
	public function count_users_returns_correct_number()
	{
		$owner = User::factory()->create(['type' => 'company']);
		// create 3 standard users, 1 client, 1 company under this owner
		User::factory()->count(3)->create([
			'type'       => 'user',
			'created_by' => $owner->id
		]);
		User::factory()->create([
			'type'       => 'client',
			'created_by' => $owner->id
		]);
		User::factory()->create([
			'type'       => 'company',
			'created_by' => $owner->id
		]);

		$this->assertSame(3, $owner->countUsers());
	}

	/**
	 ** @test
	 **
	 ** countCompany() counts companies created by this user.
	 **/
	public function count_company_returns_only_companies()
	{
		$owner = User::factory()->create(['type' => 'company']);
		User::factory()->count(2)->create(['type' => 'company', 'created_by' => $owner->id]);
		User::factory()->count(4)->create(['type' => 'user',    'created_by' => $owner->id]);

		$this->assertSame(2, $owner->countCompany());
	}

	/**
	 ** @test
	 **
	 ** countCustomers() and countVendors() count correctly.
	 **/
	public function count_customers_and_vendors()
	{
		$owner = User::factory()->create(['type' => 'company']);
		Customer::factory()->count(5)->create(['created_by' => $owner->id]);
		Vendor::factory()->count(7)->create(['created_by' => $owner->id]);

		$this->assertSame(5, $owner->countCustomers());
		$this->assertSame(7, $owner->countVendors());
	}

	/**
	 ** @test
	 **
	 ** countInvoices() and countBills() count correctly.
	 **/
	public function count_invoices_and_bills()
	{
		$owner = User::factory()->create(['type' => 'company']);
		Invoice::factory()->count(4)->create(['created_by' => $owner->id]);
		Bill::factory()->count(6)->create(['created_by' => $owner->id]);

		$this->assertSame(4, $owner->countInvoices());
		$this->assertSame(6, $owner->countBills());
	}

	/**
	 ** @test
	 **
	 ** isUser() and isClient() return proper integers.
	 **/
	public function is_user_and_is_client_flags()
	{
		$user  = User::factory()->make(['type' => 'user']);
		$client = User::factory()->make(['type' => 'client']);
		$other = User::factory()->make(['type' => 'company']);

		$this->assertSame(1, $user?->isUser());
		$this->assertSame(0, $client->isUser());
		$this->assertSame(0, $other->isUser());

		$this->assertSame(1, $client->isClient());
		$this->assertSame(0, $user?->isClient());
		$this->assertSame(0, $other->isClient());
	}

	/**
	 ** @test
	 **
	 ** getBranch, getDepartment, getDesignation, getEmployee, getLeaveType return model or null.
	 **/
	public function lookup_methods_return_correct_models_or_null()
	{
		$user = User::factory()->create();

		$branch = Branch::factory()->create();
		$this->assertEquals($branch->id, $user?->getBranch($branch->id)->id);
		$this->assertNull($user?->getBranch(9999));

		$department = Department::factory()->create();
		$this->assertEquals($department->id, $user?->getDepartment($department->id)->id);
		$this->assertNull($user?->getDepartment(9999));

		$designation = Designation::factory()->create();
		$this->assertEquals($designation->id, $user?->getDesignation($designation->id)->id);
		$this->assertNull($user?->getDesignation(9999));

		$employee = Employee::factory()->create();
		$this->assertEquals($employee->id, $user?->getEmployee($employee->id)->id);
		$this->assertNull($user?->getEmployee(9999));

		$leaveType = LeaveType::factory()->create();
		$this->assertEquals($leaveType->id, $user?->getLeaveType($leaveType->id)->id);
		$this->assertNull($user?->getLeaveType(9999));
	}

	/**
	 ** @test
	 **
	 ** clientEstimations and clientContracts are HasMany relations.
	 **/
	public function client_estimations_and_contracts_relations_are_hasmany()
	{
		$user = User::factory()->create();
		$this->assertInstanceOf(HasMany::class, $user?->clientEstimations());
		$this->assertInstanceOf(HasMany::class, $user?->clientContracts());
	}

	/**
	 ** @test
	 **
	 ** deals, leads, and clientDeals are BelongsToMany relations.
	 **/
	public function deals_and_leads_and_client_deals_relations_are_belongstomany()
	{
		$user = User::factory()->create();
		$this->assertInstanceOf(BelongsToMany::class, $user?->deals());
		$this->assertInstanceOf(BelongsToMany::class, $user?->leads());
		$this->assertInstanceOf(BelongsToMany::class, $user?->clientDeals());
	}

	/**
	 ** @test
	 **
	 ** projects relation is BelongsToMany with timestamps.
	 **/
	public function projects_relation_is_belongstomany_with_timestamps()
	{
		$user = User::factory()->create();
		$relation = $user?->projects();
		$this->assertInstanceOf(BelongsToMany::class, $relation);
		$this->assertTrue($relation->withTimestamps);
	}

	/**
	 ** @test
	 **
	 ** contacts(), todo(), and employee() relations return correct HasMany/HasOne and models.
	 **/
	public function contacts_todo_and_employee_relations_work()
	{
		$user = User::factory()->create();

		$contact = UserContact::factory()->create(['parent_id' => $user?->id]);
		$todo   = UserToDo::factory()->create(['user_id'    => $user?->id]);
		$emp    = Employee::factory()->create(['user_id'    => $user?->id]);

		// Relation type assertions
		$this->assertInstanceOf(HasMany::class, $user?->contacts());
		$this->assertInstanceOf(HasMany::class, $user?->todo());
		$this->assertInstanceOf(HasOne::class,   $user?->employee());

		// Data assertions
		$this->assertEquals($contact->id, $user?->contacts->first()->id);
		$this->assertEquals($todo->id,    $user?->todo->first()->id);
		$this->assertEquals($emp->id,     $user?->employee->id);
	}

	/**
	 ** @test
	 **
	 ** clientProjects() relation returns a HasMany and loads correct Project models.
	 **/
	public function client_projects_relation_returns_projects()
	{
		$user   = User::factory()->create();
		$project = Project::factory()->create(['client_id' => $user?->id]);

		$this->assertInstanceOf(HasMany::class, $user?->clientProjects());
		$this->assertEquals($project->id, $user?->clientProjects->first()->id);
	}

	/**
	 ** @test
	 **
	 ** tasks() returns company tasks when user is company.
	 **/
	public function tasks_returns_all_tasks_for_company()
	{
		$company = User::factory()->create(['type' => 'company']);
		Auth::login($company);
		ProjectTask::factory()->count(3)->create(['created_by' => $company->id]);
		$this->assertCount(3, $company->tasks());
	}

	/**
	 ** @test
	 **
	 ** tasks() returns assigned tasks for non-company user.
	 **/
	public function tasks_returns_assigned_tasks_for_non_company()
	{
		$user = User::factory()->create(['type' => 'user']);
		Auth::login($user);
		// assign_to stored as comma-separated IDs
		$t1 = ProjectTask::factory()->create(['assign_to' => "{$user?->id}"]);
		$t2 = ProjectTask::factory()->create(['assign_to' => "{$user?->id},999"]);
		$t3 = ProjectTask::factory()->create(['assign_to' => "999"]);
		$tasks = $user?->tasks();
		$this->assertTrue($tasks->contains('id', $t1->id));
		$this->assertTrue($tasks->contains('id', $t2->id));
		$this->assertFalse($tasks->contains('id', $t3->id));
	}

	/**
	 ** @test
	 **
	 ** bugNumberFormat returns prefix plus zero-padded number.
	 **/
	public function bug_number_format_uses_settings()
	{
		// simulate settings
		Helper::storeSetting('bug_prefix', 'BG-');
		$user = User::factory()->create();
		$this->assertEquals('BG-00042', $user?->bugNumberFormat(42));
	}

	/**
	 ** @test
	 **
	 ** extraKeyword returns non-empty array including 'Sun' and 'Cashflow'.
	 **/
	public function extra_keyword_contains_expected_entries()
	{
		$user = User::factory()->create();
		$keywords = $user?->extraKeyword();
		$this->assertIsArray($keywords);
		$this->assertContains('Sun', $keywords);
		$this->assertContains('Cashflow', $keywords);
	}

	/**
	 ** @test
	 **
	 ** barcodeFormat and barcodeType fall back to defaults when settings missing.
	 **/
	public function barcode_format_and_type_defaults()
	{
		Helper::clearSetting('barcode_format');
		Helper::clearSetting('barcode_type');
		$user = User::factory()->create();
		$this->assertEquals('code128', $user?->barcodeFormat());
		$this->assertEquals('css',     $user?->barcodeType());
	}

	/**
	 ** @test
	 **
	 ** employeeIdFormat prefixes number correctly.
	 **/
	public function employee_id_format_uses_setting()
	{
		Helper::storeSetting('employee_prefix', 'EMP-');
		$this->assertEquals('EMP-00007', User::employeeIdFormat(7));
	}

	/**
	 ** @test
	 **
	 ** userCurrentLocation returns location id for company user.
	 **/
	public function user_current_location_for_company()
	{
		$company = User::factory()->create(['type' => 'company', 'current_location' => null]);
		Auth::login($company);
		$loc = Location::factory()->create(['company_id' => $company->id, 'is_active' => 1]);
		$company->current_location = $loc->id;
		$this->assertEquals($loc->id, User::userCurrentLocation());
	}

	/**
	 ** @test
	 **
	 ** countEmployees counts correctly.
	 **/
	public function count_employees_counts_created_by()
	{
		$owner = User::factory()->create(['type' => 'company']);
		Auth::login($owner);
		\App\Models\Employee::factory()->count(4)->create(['created_by' => $owner->id]);
		$this->assertSame(4, $owner->countEmployees());
	}

	/**
	 ** @test
	 **
	 ** priceFormat() and static priceFormats() produce the same formatted output.
	 **/
	public function price_formatting_methods_work()
	{
		$user = User::factory()->create(['created_by' => 1]);

		$this->assertSame('$1,234.50', $user?->priceFormat(1234.5));
		$this->assertSame('$1,234.50', User::priceFormats(1234.5));
	}

	/**
	 ** @test
	 **
	 ** currencySymbol(), dateFormat(), and timeFormat() respect settings.
	 **/
	public function simple_formatting_helpers()
	{
		$user = User::factory()->create(['created_by' => 1]);

		$this->assertSame('$',      $user?->currencySymbol());
		$this->assertSame('29/05/2025', $user?->dateFormat('2025-05-29'));
		$this->assertSame('14:30',  $user?->timeFormat('14:30:00'));
	}

	/**
	 ** @test
	 **
	 ** All numbered prefixes zero-pad correctly.
	 **/
	public function all_number_formatters_prefix_zero_pad()
	{
		$user = User::factory()->create(['created_by' => 1]);

		$this->assertSame('PU-00007', $user?->purchaseNumberFormat(7));
		$this->assertSame('POS-00007', $user?->posNumberFormat(7));
		$this->assertSame('INV-00007', $user?->invoiceNumberFormat(7));
		$this->assertSame('PR-00007', $user?->proposalNumberFormat(7));
		$this->assertSame('C-00007',  $user?->contractNumberFormat(7));
		$this->assertSame('B-00007',  $user?->billNumberFormat(7));
		$this->assertSame('E-00007',  $user?->expenseNumberFormat(7));
		$this->assertSame('J-00007',  $user?->journalNumberFormat(7));
	}

	/**
	 ** @test
	 **
	 ** getPlan() returns a HasOne relation.
	 **/
	public function plan_relation_returns_hasone()
	{
		$this->assertInstanceOf(HasOne::class, (new User())->getPlan());
	}

	/**
	 ** @test
	 **
	 ** countOrder(), countPlan(), and countPaidCompany() return zero on an empty database.
	 **/
	public function count_methods_on_empty_database()
	{
		$company = User::factory()->create(['type' => 'company']);
		$this->assertSame(0, $company->countOrder());
		$this->assertSame(0, $company->countPlan());
		$this->assertSame(0, $company->countPaidCompany());
	}

	/**
	 ** @test
	 **
	 ** All financial report methods return zero or empty arrays when no data exists.
	 **/
	public function financial_reports_are_zero_when_empty()
	{
		$company = User::factory()->create(['type' => 'company']);
		Auth::login($company);

		// todayIncome and todayExpense
		$this->assertSame(0.0, $company->todayIncome());
		$this->assertSame(0.0, $company->todayExpense());

		// incomeCurrentMonth and expenseCurrentMonth
		$this->assertSame(0.0, $company->incomeCurrentMonth());
		$this->assertSame(0.0, $company->expenseCurrentMonth());

		// incomeCat
		$this->assertSame(0.0, $company->incomeCat());

		// getIncExpBarChartData keys and zero values
		$bar = $company->getIncExpBarChartData();
		$this->assertCount(12, $bar['month']);
		$this->assertCount(12, $bar['income']);
		$this->assertCount(12, $bar['expense']);
		$this->assertTrue(collect($bar['income'])->every(fn ($v) => $v === 0.0));
		$this->assertTrue(collect($bar['expense'])->every(fn ($v) => $v === 0.0));

		// getIncExpLineChartDate keys and zero values
		$line = $company->getIncExpLineChartDate();
		$this->assertCount(15, $line['day']);
		$this->assertCount(15, $line['income']);
		$this->assertCount(15, $line['expense']);
		$this->assertTrue(collect($line['income'])->every(fn ($v) => $v === 0.0));
		$this->assertTrue(collect($line['expense'])->every(fn ($v) => $v === 0.0));

		// weeklyInvoice, monthlyInvoice, weeklyBill, monthlyBill
		foreach (['weeklyInvoice', 'monthlyInvoice'] as $method) {
			$res = $company->$method();
			$this->assertEquals(['invoiceTotal' => 0.0, 'invoicePaid' => 0.0, 'invoiceDue' => 0.0], $res);
		}
		foreach (['weeklyBill', 'monthlyBill'] as $method) {
			$res = $company->$method();
			$this->assertEquals(['billTotal' => 0.0, 'billPaid' => 0.0, 'billDue' => 0.0], $res);
		}
	}

	/**
	 ** @test
	 **
	 ** defaultEmail seeds templates and their language entries.
	 **/
	public function default_email_seeds_templates_and_langs()
	{
		// Ensure empty
		$this->assertSame(0, EmailTemplate::count());
		$this->assertSame(0, EmailTemplateLang::count());

		User::defaultEmail();

		$tplCount = EmailTemplate::count();
		$langCount = EmailTemplateLang::count();

		$this->assertGreaterThan(0, $tplCount);
		$this->assertGreaterThanOrEqual($tplCount, $langCount);

		// Every template has at least one lang
		EmailTemplate::all()->each(function ($tpl) {
			$this->assertTrue(
				EmailTemplateLang::where('parent_id', $tpl->id)->exists()
			);
		});
	}

	/**
	 ** @test
	 **
	 ** userDefaultData and userDefaultDataRegister seed user-email-template rows.
	 **/
	public function user_default_data_methods_create_user_email_templates()
	{
		// seed templates
		User::defaultEmail();
		$templateIds = EmailTemplate::pluck('id')->toArray();

		// userDefaultData creates for user_id = 2
		User::factory()->create(['id' => 2]);
		User::userDefaultData();
		$this->assertSame(
			count($templateIds),
			UserEmailTemplate::where('user_id', 2)->count()
		);

		// for a custom user
		$u = User::factory()->create();
		$u->userDefaultDataRegister($u->id);
		$this->assertSame(
			count($templateIds),
			UserEmailTemplate::where('user_id', $u->id)->count()
		);
	}

	/**
	 ** @test
	 **
	 ** userDefaultWarehouse and userWarehouseRegister create warehouses.
	 **/
	public function warehouse_and_bank_account_seeders()
	{
		// default for user_id=2
		User::userDefaultWarehouse();
		$this->assertDatabaseHas('warehouses', ['created_by' => 2, 'name' => 'North Warehouse']);

		$u = User::factory()->create();
		$u->userWarehouseRegister($u->id);
		$this->assertDatabaseHas('warehouses', ['created_by' => $u->id]);

		// bank account for custom user
		$u2 = User::factory()->create();
		$u->userDefaultBankAccount($u2->id);
		$this->assertDatabaseHas('bank_accounts', ['created_by' => $u2->id, 'holder_name' => 'cash']);
	}

	/**
	 ** @test
	 **
	 ** showDashboard and show* static methods return correct plan or feature strings.
	 **/
	public function show_dashboard_and_feature_shortcuts()
	{
		$plan = Plan::factory()->create([
			'crm' => 'crm-feature',
			'hrm' => 'hrm-feature',
			'account' => 'acc-feature',
			'project' => 'proj-feature',
			'pos' => 'pos-feature'
		]);

		$company = User::factory()->create(['type' => 'company', 'plan' => $plan->id]);
		Auth::login($company);

		// showDashboard returns plan id
		$this->assertSame($plan->id, $company->showDashboard());

		$this->assertSame('crm-feature',    User::showCrm());
		$this->assertSame('hrm-feature',    User::showHrm());
		$this->assertSame('acc-feature',    User::showAccount());
		$this->assertSame('proj-feature',   User::showProject());
		$this->assertSame('pos-feature',    User::showPos());
	}

	/**
	 ** @test
	 **
	 ** totalCompany* methods return counts correctly.
	 **/
	public function total_company_counters_work()
	{
		$owner = User::factory()->create();
		Customer::factory()->count(3)->create(['created_by' => $owner->id]);
		Vendor::factory()->count(5)->create(['created_by' => $owner->id]);
		User::factory()->count(2)->create(['created_by' => $owner->id]);

		$this->assertSame(2, $owner->totalCompanyUser($owner->id));
		$this->assertSame(3, $owner->totalCompanyCustomer($owner->id));
		$this->assertSame(5, $owner->totalCompanyVendor($owner->id));
	}

	/**
	 ** @test
	 **
	 ** planPrice returns all settings for the correct user scope.
	 **/
	public function plan_price_returns_scope_settings()
	{
		// create settings for user id 1
		DB::table('settings')->insert([
			['name' => 'foo', 'value' => 'bar', 'created_by' => 1],
			['name' => 'baz', 'value' => 'qux', 'created_by' => 1],
		]);

		$user = User::factory()->create(['type' => 'company', 'id' => 1]);
		Auth::login($user);

		$prices = $user?->planPrice();
		$this->assertSame(['foo' => 'bar', 'baz' => 'qux'], $prices);
	}

	/**
	 ** @test
	 **
	 ** checkProject always returns "Owner".
	 **/
	public function check_project_always_returns_owner()
	{
		$user = User::factory()->create();
		$this->assertSame('Owner', $user?->checkProject(123));
	}

	/**
	 ** @test
	 **
	 ** assignPlan returns error when plan does not exist.
	 **/
	public function assign_plan_with_invalid_id_returns_error()
	{
		$company = User::factory()->create(['type' => 'company']);
		Auth::login($company);

		$result = $company->assignPlan(9999);
		$this->assertFalse($result['is_success']);
		$this->assertEquals('Plan is deleted.', $result['error']);
	}

	/**
	 ** @test
	 **
	 ** assignPlan respects max limits: only first N items remain active.
	 **/
	public function assign_plan_respects_maximum_limits()
	{
		$company = User::factory()->create(['type' => 'company']);
		Auth::login($company);

		// Create a plan limiting to 2 users, 1 client, 1 customer, 1 vendor
		$plan = Plan::factory()->create([
			'max_users'     => 2,
			'max_clients'   => 1,
			'max_customers' => 1,
			'max_vendors'   => 1,
			'duration'      => 'month',
		]);

		// Create 4 regular users under this company
		$users = User::factory()->count(4)->create(['created_by' => $company->id, 'type' => 'user']);
		// Create 3 clients under this company
		$clients = User::factory()->count(3)->create(['created_by' => $company->id, 'type' => 'client']);
		// Create 2 customers
		$customers = Customer::factory()->count(2)->create(['created_by' => $company->id]);
		// Create 2 vendors
		$vendors = Vendor::factory()->count(2)->create(['created_by' => $company->id]);

		$result = $company->assignPlan($plan->id);
		$this->assertTrue($result['is_success']);

		// After sync: only first 2 users active, rest inactive
		$activeUsers = User::where('created_by', $company->id)
			->whereNotIn('type', ['super admin', 'company', 'client'])
			->where('is_active', 1)
			->count();
		$this->assertSame(2, $activeUsers);

		// Only first client active
		$activeClients = User::where('created_by', $company->id)
			->where('type', 'client')
			->where('is_active', 1)
			->count();
		$this->assertSame(1, $activeClients);

		// Only first customer active
		$activeCustomers = Customer::where('created_by', $company->id)
			->where('is_active', 1)
			->count();
		$this->assertSame(1, $activeCustomers);

		// Only first vendor active
		$activeVendors = Vendor::where('created_by', $company->id)
			->where('is_active', 1)
			->count();
		$this->assertSame(1, $activeVendors);
	}

	/**
	 ** @test
	 **
	 ** authId, creatorId, ownerId, and ownerDetails behave correctly.
	 **/
	public function auth_and_creator_and_owner_methods()
	{
		$company = User::factory()->create(['type' => 'company']);
		$sub    = User::factory()->create(['type' => 'user', 'created_by' => $company->id]);

		// Company user
		$this->assertEquals($company->id, $company->authId());
		$this->assertEquals($company->id, $company->creatorId());
		$this->assertEquals($company->id, $company->ownerId());
		$this->assertEquals($company->id, $company->ownerDetails()->id);

		// Sub-user
		$this->assertEquals($sub->id,        $sub->authId());
		$this->assertEquals($company->id,   $sub->creatorId());
		$this->assertEquals($company->id,   $sub->ownerId());
		$this->assertEquals($company->id,   $sub->ownerDetails()->id);
	}

	/**
	 ** @test
	 **
	 ** currentLanguage reflects the lang attribute.
	 **/
	public function current_language_is_returned()
	{
		$user = User::factory()->create(['lang' => 'pt']);
		$this->assertSame('pt', $user?->currentLanguage());
	}

	/**
	 ** @test
	 **
	 ** getProfileAttribute returns default and custom avatar URLs.
	 **/
	public function profile_attribute_falls_back_and_uses_custom()
	{
		Storage::fake('public');
		// default
		$user = User::factory()->create(['avatar' => null]);
		$url = $user?->getProfileAttribute();
		$this->assertStringContainsString('avatar.png', $url);

		// custom
		Storage::disk('public')->put('avatars/custom.png', '');
		$user->avatar = 'avatars/custom.png';
		$customUrl = $user?->getProfileAttribute();
		$this->assertStringContainsString('custom.png', $customUrl);
	}

	/**
	 ** @test
	 **
	 ** countUsers, countCompany, countCustomers, countVendors return correct counts.
	 **/
	public function various_count_methods()
	{
		$company = User::factory()->create(['type' => 'company']);
		Auth::login($company);

		// subordinate users
		User::factory()->count(3)->create(['created_by' => $company->id, 'type' => 'user']);
		User::factory()->count(2)->create(['created_by' => $company->id, 'type' => 'company']);

		Customer::factory()->count(4)->create(['created_by' => $company->id]);
		Vendor::factory()->count(5)->create(['created_by' => $company->id]);

		$this->assertSame(3, $company->countUsers());
		$this->assertSame(2, $company->countCompany());
		$this->assertSame(4, $company->countCustomers());
		$this->assertSame(5, $company->countVendors());
	}

	/**
	 ** @test
	 **
	 ** getBranch, getDepartment, getDesignation, getEmployee, getLeaveType
	 ** return the model when it exists and null otherwise.
	 **/
	public function it_fetches_related_models_or_null()
	{
		$user = User::factory()->create();

		$branch = Branch::factory()->create();
		$this->assertInstanceOf(Branch::class, $user?->getBranch($branch->id));
		$this->assertNull($user?->getBranch(9999));

		$dept = Department::factory()->create();
		$this->assertInstanceOf(Department::class, $user?->getDepartment($dept->id));
		$this->assertNull($user?->getDepartment(9999));

		$desig = Designation::factory()->create();
		$this->assertInstanceOf(Designation::class, $user?->getDesignation($desig->id));
		$this->assertNull($user?->getDesignation(9999));

		$emp = Employee::factory()->create();
		$this->assertInstanceOf(Employee::class, $user?->getEmployee($emp->id));
		$this->assertNull($user?->getEmployee(9999));

		// LeaveType factory may not exist; expect null for missing
		$this->assertNull($user?->getLeaveType(12345));
	}

	/**
	 ** @test
	 **
	 ** clientEstimations and clientContracts return only those linked to user.
	 **/
	public function client_related_relations()
	{
		$client = User::factory()->create(['type' => 'client']);
		Auth::login($client);

		$e1 = \App\Models\Estimation::factory()->create(['client_id' => $client->id]);
		$e2 = \App\Models\Estimation::factory()->create(['client_id' => $client->id]);
		\App\Models\Estimation::factory()->create(); // other

		$c1 = \App\Models\Contract::factory()->create(['client_name' => $client->id]);
		\App\Models\Contract::factory()->create();

		$this->assertCount(2, $client->clientEstimations);
		$this->assertCount(1, $client->clientContracts);
	}

	/**
	 ** @test
	 **
	 ** deals, leads, and clientDeals many-to-many return attached records.
	 **/
	public function many_to_many_relations()
	{
		$user = User::factory()->create();
		Auth::login($user);

		$d1 = Deal::factory()->create();
		$d2 = Deal::factory()->create();
		$user?->deals()->attach([$d1->id, $d2->id]);
		$this->assertCount(2, $user?->deals);

		$l1 = Lead::factory()->create();
		$l2 = Lead::factory()->create();
		$user?->leads()->attach([$l1->id, $l2->id]);
		$this->assertCount(2, $user?->leads);

		// clientDeals pivot
		$cd1 = Deal::factory()->create();
		$cd2 = Deal::factory()->create();
		$user?->clientDeals()->attach([$cd1->id, $cd2->id]);
		$this->assertCount(2, $user?->clientDeals);
	}

	/**
	 ** @test
	 **
	 ** userCurrentLocation returns the correct id for non-company users.
	 **/
	public function user_current_location_for_non_company()
	{
		$company = User::factory()->create(['type' => 'company']);
		$loc    = Location::factory()->create(['company_id' => $company->id, 'is_active' => 1]);

		$user = User::factory()->create([
			'type'             => 'user',
			'created_by'       => $company->id,
			'current_location' => 0,
			'location_id'      => $loc->id,
		]);
		Auth::login($user);

		$this->assertEquals($loc->id, User::userCurrentLocation());
	}

	/**
	 ** @test
	 **
	 ** employeeIdFormat returns prefix and zero-padded number.
	 **/
	public function employee_prefix_formatter_works()
	{
		$this->assertSame('EMP-00042', User::employeeIdFormat(42));
	}

	/**
	 ** @test
	 **
	 ** customerNumberFormat and vendorNumberFormat honor settings.
	 **/
	public function customer_and_vendor_formatters_work()
	{
		$company = User::factory()->create(['created_by' => 1]);
		$this->assertSame('CUST-00007', $company->customerNumberFormat(7));
		$this->assertSame('VEND-00007', $company->vendorNumberFormat(7));
	}

	/**
	 ** @test
	 **
	 ** bugNumberFormat uses the bug_prefix setting.
	 **/
	public function bug_number_formatter_uses_setting()
	{
		$user = User::factory()->create(['created_by' => 1]);
		$this->assertSame('BUG-00009', $user?->bugNumberFormat(9));
	}

	/**
	 ** @test
	 **
	 ** extraKeyword returns an array containing known entries.
	 **/
	public function extra_keywords_contain_expected_terms()
	{
		$keywords = (new User())->extraKeyword();
		$this->assertIsArray($keywords);
		$this->assertContains('Sun', $keywords);
		$this->assertContains('Coupon', $keywords);
	}

	/**
	 ** @test
	 **
	 ** barcodeFormat and barcodeType pull from settings and fall back correctly.
	 **/
	public function barcode_format_and_type_methods_work()
	{
		$user = User::factory()->create(['created_by' => 1]);

		// from seeded settings
		$this->assertSame('code39', $user?->barcodeFormat());
		$this->assertSame('svg',    $user?->barcodeType());

		// delete to force defaults
		DB::table('settings')->where('name', 'barcode_format')->delete();
		DB::table('settings')->where('name', 'barcode_type')->delete();

		$this->assertSame('code128', $user?->barcodeFormat());
		$this->assertSame('css',     $user?->barcodeType());
	}

	/**
	 ** @test
	 **
	 ** countEmployees returns the correct number of Employee records.
	 **/
	public function count_employees_returns_correct_value()
	{
		$company = User::factory()->create();
		Employee::factory()->count(5)->create(['created_by' => $company->id]);
		$this->assertSame(5, $company->countEmployees());
	}

	/**
	 ** @test
	 **
	 ** priceFormat and static priceFormats format with symbol, decimals & position.
	 **/
	public function price_formatters_honor_settings()
	{
		$user = User::factory()->create(['type' => 'company', 'id' => 1]);
		Auth::login($user);

		// instance method
		$formatted = $user?->priceFormat(1234.5);
		$this->assertSame('$1,234.50', $formatted);

		// static
		$static = User::priceFormats(67.8);
		$this->assertSame('$67.80', $static);
	}

	/**
	 ** @test
	 **
	 ** currencySymbol returns the configured symbol.
	 **/
	public function currency_symbol_method_reads_setting()
	{
		$user = User::factory()->create();
		$this->assertSame('$', $user?->currencySymbol());
	}

	/**
	 ** @test
	 **
	 ** dateFormat and timeFormat use the configured formats.
	 **/
	public function date_and_time_format_methods()
	{
		$user = User::factory()->create();
		$this->assertSame('2025/05/29', $user?->dateFormat('2025-05-29'));
		// the time format uses 'H|i', so separator should be '|'
		$this->assertSame('15|30', $user?->timeFormat('15:30:00'));
	}

	/**
	 ** @test
	 **
	 ** various number formatters apply prefixes and zero-pad to 5 digits.
	 **/
	public function all_number_formatters_apply_prefixes()
	{
		$user = User::factory()->create(['type' => 'company', 'id' => 1]);

		$this->assertSame('PUR-00012', $user?->purchaseNumberFormat(12));
		$this->assertSame('POS-00012', $user?->posNumberFormat(12));
		$this->assertSame('INV-00012', $user?->invoiceNumberFormat(12));
		$this->assertSame('PRO-00012', $user?->proposalNumberFormat(12));
		$this->assertSame('CTR-00012', $user?->contractNumberFormat(12));
		$this->assertSame('BIL-00012', $user?->billNumberFormat(12));
		$this->assertSame('EXP-00012', $user?->expenseNumberFormat(12));
		$this->assertSame('JRN-00012', $user?->journalNumberFormat(12));
	}

	/** 
	 ** @test
	 **
	 ** todayIncome and todayExpense sum revenue+invoices and payment+bills.
	 **/
	public function today_income_and_expense_are_calculated()
	{
		$user = User::factory()->create();
		Auth::login($user);
		$uid = $user?->creatorId();

		// Revenue today = 100
		Revenue::factory()->create(['created_by' => $uid, 'amount' => 100, 'date' => now()]);
		// Invoice today: getTotal returns 'total' field
		Invoice::factory()->create(['created_by' => $uid, 'send_date' => now(), 'total' => 50, 'due' => 10]);

		$this->assertEquals(100 + 50, $user?->todayIncome());

		// Payment today = 30
		Payment::factory()->create(['created_by' => $uid, 'amount' => 30, 'date' => now()]);
		Bill::factory()->create(['created_by' => $uid, 'send_date' => now(), 'total' => 20, 'due' => 5]);

		$this->assertEquals(30 + 20, $user?->todayExpense());
	}

	/**
	 ** @test
	 **
	 ** incomeCurrentMonth and expenseCurrentMonth sum month data.
	 **/
	public function monthly_income_and_expense_are_calculated()
	{
		$user = User::factory()->create();
		Auth::login($user);
		$uid = $user?->creatorId();
		// Create last month and this month
		Revenue::factory()->create([
			'created_by' => $uid,
			'amount' => 200,
			'date' => now()->startOfMonth()->addDays(1)
		]);
		Invoice::factory()->create([
			'created_by' => $uid,
			'send_date' => now()->startOfMonth()->addDays(2),
			'total' => 80,
			'due' => 0
		]);
		Payment::factory()->create([
			'created_by' => $uid,
			'amount' => 40,
			'date' => now()->startOfMonth()->addDays(3)
		]);
		Bill::factory()->create([
			'created_by' => $uid,
			'send_date' => now()->startOfMonth()->addDays(4),
			'total' => 60,
			'due' => 0
		]);

		$this->assertEquals(200 + 80, $user?->incomeCurrentMonth());
		$this->assertEquals(40 + 60, $user?->expenseCurrentMonth());
	}

	/**
	 ** @test
	 **
	 ** getIncExpBarChartData and getIncExpLineChartDate return correct shapes.
	 **/
	public function chart_data_helpers_return_expected_arrays()
	{
		$company = User::factory()->create(['type' => 'company']);
		Auth::login($company);

		$bar = $company->getIncExpBarChartData();
		$this->assertCount(12, $bar['month']);
		$this->assertCount(12, $bar['income']);
		$this->assertCount(12, $bar['expense']);

		$line = $company->getIncExpLineChartDate();
		$this->assertCount(15, $line['day']);
		$this->assertCount(15, $line['income']);
		$this->assertCount(15, $line['expense']);
	}

	/**
	 ** @test
	 **
	 ** weeklyInvoice/monthlyInvoice and weeklyBill/monthlyBill produce totals.
	 **/
	public function weekly_and_monthly_summary_methods()
	{
		$user = User::factory()->create();
		Auth::login($user);
		$uid = $user?->creatorId();

		// invoice in last week and month
		Invoice::factory()->create([
			'created_by' => $uid,
			'issue_date' => now()->subDays(3),
			'total' => 70, 'due' => 20
		]);

		$weekInv = $user?->weeklyInvoice();
		$this->assertEquals(70, $weekInv['invoiceTotal']);
		$this->assertEquals(70 - 20, $weekInv['invoicePaid']);
		$this->assertEquals(20, $weekInv['invoiceDue']);

		$monthInv = $user?->monthlyInvoice();
		$this->assertEquals(70, $monthInv['invoiceTotal']);

		// bill
		Bill::factory()->create([
			'created_by' => $uid,
			'bill_date' => now()->subDays(2),
			'total' => 40, 'due' => 10
		]);
		$weekBill = $user?->weeklyBill();
		$this->assertEquals(40, $weekBill['billTotal']);
		$this->assertEquals(30, $weekBill['billPaid']);
		$this->assertEquals(10, $weekBill['billDue']);

		$monthBill = $user?->monthlyBill();
		$this->assertEquals(40, $monthBill['billTotal']);
	}

	/**
	 ** @test
	 **
	 ** countOrder, countPlan, countPaidCompany work correctly.
	 **/
	public function simple_count_methods_return_totals()
	{
		Order::factory()->count(3)->create();
		Plan::factory()->count(2)->create();

		$company = User::factory()->create(['type' => 'company']);
		Auth::login($company);

		$this->assertSame(3, $company->countOrder());
		$this->assertSame(2, $company->countPlan());

		// paid companies
		User::factory()->create(['type' => 'company', 'plan' => 5, 'created_by' => $company->id]);
		User::factory()->create(['type' => 'company', 'plan' => 0, 'created_by' => $company->id]);
		$this->assertSame(1, $company->countPaidCompany());
	}

	/**
	 ** @test
	 **
	 ** totalLead varies by user type.
	 **/
	public function total_lead_counts_per_user_type()
	{
		$owner = User::factory()->create(['type' => 'company']);
		Auth::login($owner);
		Lead::factory()->count(4)->create(['created_by' => $owner->id]);
		$this->assertSame(4, $owner->totalLead());

		$client = User::factory()->create(['type' => 'client', 'created_by' => $owner->id]);
		Auth::login($client);
		Lead::factory()->count(3)->create(['client' => $client->id]);
		$this->assertSame(3, $client->totalLead());

		$user = User::factory()->create(['type' => 'user', 'created_by' => $owner->id]);
		Auth::login($user);
		Lead::factory()->count(2)->create(['owner' => $user?->id]);
		$this->assertSame(2, $user?->totalLead());
	}

	/**
	 ** @test
	 **
	 ** project/task related counts and retrievals.
	 **/
	public function project_task_helpers_behave_as_expected()
	{
		$company = User::factory()->create(['type' => 'company']);
		Auth::login($company);
		$uid = $company->creatorId();

		$proj = Project::factory()->create(['created_by' => $uid]);
		$stage = TaskStage::factory()->create(['created_by' => $uid, 'order' => 1]);
		// tasks: one complete (stage1), one incomplete
		ProjectTask::factory()->create(['project_id' => $proj->id, 'stage_id' => $stage->id]);
		ProjectTask::factory()->create([
			'project_id' => $proj->id,
			'stage_id'   => 2,
			'end_date'   => now()->addDay()
		]);

		// clientProjects
		$this->assertEmpty($company->clientProjects);

		// lastProjectStage
		$this->assertEquals($stage->id, $company->lastProjectStage()->id);

		// userProject
		$this->assertSame(1, $company->userProject());

		// createdTotalProjectTask (company)
		$this->assertSame(2, $company->createdTotalProjectTask());

		// projectCompleteTask
		$this->assertSame(1, $company->projectCompleteTask($stage->id));

		// createdTopDueTask
		$due = $company->createdTopDueTask();
		$this->assertCount(1, $due);
	}

	/**
	 ** @test
	 **
	 ** defaultEmail creates templates and languages when missing.
	 **/
	public function default_email_seeder_populates_templates_and_langs()
	{
		// Ensure tables empty
		$this->assertEquals(0, EmailTemplate::count());
		$this->assertEquals(0, EmailTemplateLang::count());

		// Invoke seeder helper
		User::defaultEmail();

		// Expect at least the named templates created
		$this->assertGreaterThan(0, EmailTemplate::count());
		// Each template should have at least one lang entry
		$firstTpl = EmailTemplate::first();
		$this->assertTrue(EmailTemplateLang::where('parent_id', $firstTpl->id)->exists());
	}

	/**
	 ** @test
	 **
	 ** userDefaultData and userDefaultDataRegister create UserEmailTemplate entries.
	 **/
	public function user_default_data_helpers_create_user_email_templates()
	{
		$templates = EmailTemplate::factory()->count(3)->create();
		$user2 = User::factory()->create(['id' => 2]);
		// global default
		User::userDefaultData();
		$this->assertEquals(3, UserEmailTemplate::where('user_id', 2)->count());

		// per-user register
		$user5 = User::factory()->create(['id' => 5]);
		$user5->userDefaultDataRegister(5);
		$this->assertEquals(3, UserEmailTemplate::where('user_id', 5)->count());
	}

	/**
	 ** @test
	 **
	 ** userDefaultWarehouse and userWarehouseRegister insert a warehouse record.
	 **/
	public function warehouse_helpers_create_records()
	{
		// global default
		User::userDefaultWarehouse();
		$this->assertDatabaseHas('warehouses', ['name' => 'North Warehouse', 'created_by' => 2]);

		// per-user register
		$user9 = User::factory()->create(['id' => 9]);
		$user9->userWarehouseRegister(9);
		$this->assertDatabaseHas('warehouses', ['name' => 'North Warehouse', 'created_by' => 9]);
	}

	/**
	 ** @test
	 **
	 ** userDefaultBankAccount creates a bank account record.
	 **/
	public function bank_account_helper_creates_record()
	{
		$user7 = User::factory()->create(['id' => 7]);
		$user7->userDefaultBankAccount(7);
		$this->assertDatabaseHas('bank_accounts', [
			'holder_name'    => 'cash',
			'created_by'     => 7
		]);
	}

	/**
	 ** @test
	 **
	 ** assignPlan enforces max counts and toggles is_active flags.
	 **/
	public function assign_plan_limits_entities_correctly()
	{
		Carbon::setTestNow('2025-05-29');

		// Owner / company
		$company = User::factory()->create(['type' => 'company']);
		Auth::login($company);

		// create Plan: 2 users, 1 client, 1 customer, 1 vendor
		$plan = Plan::factory()->create([
			'max_users'     => 2,
			'max_clients'   => 1,
			'max_customers' => 1,
			'max_vendors'   => 1,
			'duration'      => 'month'
		]);

		// spin up entities that exceed limits
		// Users
		$u1 = $this->quickUser('user',   $company->id);
		$u2 = $this->quickUser('user',   $company->id);
		$u3 = $this->quickUser('user',   $company->id);         // should be disabled
		// Clients
		$c1 = $this->quickUser('client', $company->id);
		$c2 = $this->quickUser('client', $company->id);         // should be disabled
		// Customers
		Customer::factory()->create(['created_by' => $company->id]);
		Customer::factory()->create(['created_by' => $company->id]); // disabled
		// Vendors
		Vendor::factory()->create(['created_by' => $company->id]);
		Vendor::factory()->create(['created_by' => $company->id]);   // disabled

		// Act
		$result = $company->assignPlan($plan->id);

		$this->assertTrue($result['is_success']);
		$company->refresh();
		$this->assertEquals(
			Carbon::now()->addMonth()->isoFormat('YYYY-MM-DD'),
			$company->plan_expire_date
		);

		// first two users active, 3rd inactive
		$this->assertEquals([1, 1, 0], User::whereIn('id', [$u1->id, $u2->id, $u3->id])->pluck('is_active')->toArray());
		// clients – only first remains active
		$this->assertEquals([1, 0],  User::whereIn('id', [$c1->id, $c2->id])->pluck('is_active')->toArray());
		// customers
		$this->assertEquals([1, 0],  Customer::orderBy('id')->pluck('is_active')->toArray());
		// vendors
		$this->assertEquals([1, 0],  Vendor::orderBy('id')->pluck('is_active')->toArray());
	}

	/**
	 ** @test
	 **
	 ** _syncActive respects unlimited (-1) sentinel.
	 **/
	public function sync_active_with_unlimited_does_not_disable_anything()
	{
		$owner = User::factory()->create(['type' => 'company']);
		Auth::login($owner);

		$plan = Plan::factory()->create([
			'max_users'   => -1,
			'max_clients' => -1,
			'max_customers' => -1,
			'max_vendors'   => -1,
			'duration' => 'month'
		]);

		// 3 employees & 2 clients
		$e = User::factory()->count(3)->create(['type' => 'user',   'created_by' => $owner->id, 'is_active' => 0]);
		$c = User::factory()->count(2)->create(['type' => 'client', 'created_by' => $owner->id, 'is_active' => 0]);
		Customer::factory()->count(2)->create(['created_by' => $owner->id, 'is_active' => 0]);
		Vendor::factory()->count(2)->create(['created_by' => $owner->id, 'is_active' => 0]);

		$owner->assignPlan($plan->id);

		// everything toggled to active
		$this->assertEquals(5, User::where('created_by', $owner->id)->where('is_active', 1)->count());
		$this->assertEquals(2, Customer::where('created_by', $owner->id)->where('is_active', 1)->count());
		$this->assertEquals(2, Vendor::where('created_by', $owner->id)->where('is_active', 1)->count());
	}

	/**
	 ** @test
	 **
	 ** isUser and isClient boolean helpers.
	 **/
	public function is_user_and_is_client_helpers()
	{
		$u = User::factory()->create(['type' => 'user']);
		$c = User::factory()->create(['type' => 'client']);

		$this->assertSame(1, $u->isUser());
		$this->assertSame(0, $u->isClient());

		$this->assertSame(1, $c->isClient());
		$this->assertSame(0, $c->isUser());
	}

	/**
	 ** @test
	 **
	 ** countUsers / countCustomers / countVendors reflect DB counts.
	 **/
	public function counters_for_users_customers_vendors()
	{
		$company = User::factory()->create(['type' => 'company']);
		Auth::login($company);

		User::factory()->count(4)->create(['type' => 'user',   'created_by' => $company->id]);
		Customer::factory()->count(3)->create(['created_by' => $company->id]);
		Vendor::factory()->count(2)->create(['created_by' => $company->id]);

		$this->assertSame(4, $company->countUsers());
		$this->assertSame(3, $company->countCustomers());
		$this->assertSame(2, $company->countVendors());
	}

	/**
	 ** @test
	 **
	 ** barcodeFormat / barcodeType pick up settings overrides.
	 **/
	public function barcode_format_and_type_methods_reflect_settings()
	{
		$user = User::factory()->create();
		$this->assertSame('qrcode', $user?->barcodeFormat());
		$this->assertSame('svg',    $user?->barcodeType());
	}

	/**
	 ** @test
	 **
	 ** employeeIdFormat applies prefix and zero-padding.
	 **/
	public function employee_id_format_uses_prefix()
	{
		$formatted = User::employeeIdFormat(23);
		$this->assertSame('EMP-00023', $formatted);
	}

	/**
	 ** @test
	 **
	 ** extraKeyword returns a translated keywords array.
	 **/
	public function extra_keyword_returns_non_empty_keyword_list()
	{
		$user = User::factory()->create();
		$keywords = $user?->extraKeyword();

		$this->assertIsArray($keywords);
		$this->assertContains(__('Sun'), $keywords);
		$this->assertGreaterThan(10, count($keywords));
	}

	/**
	 ** @test
	 **
	 ** userCurrentLocation resolves correct location for a company user.
	 **/
	public function user_current_location_for_company_user()
	{
		$company = User::factory()->create(['type' => 'company', 'user_type' => 'company']);
		Auth::login($company);

		// create active location
		$loc = Location::factory()->create([
			'company_id' => $company->id,
			'is_active'  => 1,
		]);

		// force current_location attribute (not fillable but allowed for test)
		$company->forceFill(['current_location' => $loc->id])->save();

		$resolved = User::userCurrentLocation();
		$this->assertSame($loc->id, $resolved);
	}

	/**
	 ** @test
	 **
	 ** getBranch / Department / Designation return model instances when found.
	 **/
	public function helper_getters_return_expected_models()
	{
		$user = User::factory()->create();

		$branch     = Branch::factory()->create();
		$department = Department::factory()->create();
		$designation = Designation::factory()->create();

		$this->assertTrue($branch->is($user?->getBranch($branch->id)));
		$this->assertTrue($department->is($user?->getDepartment($department->id)));
		$this->assertTrue($designation->is($user?->getDesignation($designation->id)));
	}

	/** 
	 ** @test
	 **
	 ** priceFormat (instance) and priceFormats (static) honour symbol & decimals.
	 **/
	public function price_helpers_format_numbers_correctly()
	{
		$user = User::factory()->create();
		$this->assertSame('$1,234.57', $user?->priceFormat(1234.567));
		$this->assertSame('$9.90',     User::priceFormats(9.9));
	}

	/**
	 ** @test
	 **
	 ** number-format helpers prepend correct prefixes.
	 **/
	public function various_number_format_helpers()
	{
		$user = User::factory()->create();

		$this->assertSame('PUR-00010', $user?->purchaseNumberFormat(10));
		$this->assertSame('POS-00105', $user?->posNumberFormat(105));
		$this->assertSame('INV-00077', $user?->invoiceNumberFormat(77));
		$this->assertSame('PRO-00001', $user?->proposalNumberFormat(1));
		$this->assertSame('CON-12345', $user?->contractNumberFormat(12345));
		$this->assertSame('BIL-00009', $user?->billNumberFormat(9));
		$this->assertSame('EXP-00003', $user?->expenseNumberFormat(3));
		$this->assertSame('JRN-04200', $user?->journalNumberFormat(4200));
		$this->assertSame('CUS-00012', $user?->customerNumberFormat(12));
		$this->assertSame('VND-00098', $user?->vendorNumberFormat(98));
		$this->assertSame('BUG-00007', $user?->bugNumberFormat(7));      // bug_prefix comes from Utility default
	}

	/**
	 ** @test
	 **
	 ** checkProject always returns 'Owner' for now.
	 **/
	public function check_project_returns_owner()
	{
		$owner  = User::factory()->create(['type' => 'company']);
		Auth::login($owner);

		$proj = Project::factory()->create(['client_id' => 0]);
		// add a pivot record just to populate relation
		ProjectUser::create(['user_id' => $owner->id, 'project_id' => $proj->id]);

		$this->assertSame('Owner', $owner->checkProject($proj->id));
	}

	/**
	 ** @test
	 **
	 ** planPrice fetches settings scoped by creator.
	 **/
	public function plan_price_fetches_company_settings()
	{
		$company = User::factory()->create(['type' => 'company']);
		Auth::login($company);

		DB::table('settings')->insert([
			'name' => 'custom_key',
			'value' => 'xyz',
			'created_by' => $company->id
		]);
		$priceArr = $company->planPrice();
		$this->assertArrayHasKey('custom_key', $priceArr);
		$this->assertSame('xyz', $priceArr['custom_key']);
	}

	/**
	 ** @test
	 **
	 ** showDashboard reflects owner’s plan; showCrm etc read flags from Plan.
	 **/
	public function show_dashboard_and_feature_toggles()
	{
		$plan = Plan::factory()->create([
			'crm'     => 'on',
			'hrm'     => 'off',
			'account' => 'on',
			'project' => 'on',
			'pos'     => ''
		]);

		$company = User::factory()->create(['type' => 'company', 'plan' => $plan->id]);
		Auth::login($company);

		$this->assertSame($plan->id, $company->showDashboard());
		$this->assertSame('on', User::showCrm());
		$this->assertSame('off', User::showHrm());
		$this->assertSame('on', User::showAccount());
		$this->assertSame('on', User::showProject());
		$this->assertSame('',   User::showPos());
	}

	/**
	 ** @test
	 **
	 ** getImgImageAttribute returns custom avatar url if stored, else default.
	 **/
	public function get_img_image_attribute_returns_correct_avatar()
	{
		Storage::fake('public');
		$avatarFile = UploadedFile::fake()->image('me.png');
		$path = $avatarFile->storeAs('', 'me.png', 'public');

		$empUser = User::factory()->create();
		$empUser->employee()->create([
			'avatar'  => $path,
			'user_id' => $empUser->id,
			'employee_id' => 1,
			'created_by' => $empUser->id
		]);

		// Path should point to uploaded avatar
		$this->assertStringContainsString('me.png', $empUser->img_image);
		// If we remove avatar, falls back to default image
		$empUser->employee()->update(['avatar' => '']);
		$this->assertStringContainsString('avatar.png', $empUser->fresh()->img_image);
	}

	/**
	 ** @test
	 **
	 ** ownerDetails returns the correct owner (company) instance for a sub-user.
	 **/
	public function owner_details_returns_company_account()
	{
		$company = User::factory()->create(['type' => 'company']);
		$employee = User::factory()->create(['type' => 'user', 'created_by' => $company->id]);

		$this->assertTrue($company->is($employee->ownerDetails()));
	}

	/**
	 ** @test
	 **
	 ** currencySymbol / dateFormat / timeFormat adopt settings.
	 **/
	public function basic_formatting_helpers_respect_settings()
	{
		$user = User::factory()->create();

		$this->assertSame('€', $user?->currencySymbol());

		$date = Carbon::create(2025, 5, 29, 14, 25);
		$this->assertSame('29/05/2025', $user?->dateFormat($date));
		$this->assertSame('14:25',      $user?->timeFormat($date));
	}

	/**
	 ** @test
	 **
	 ** countCompany tallies company-type users for creator scope.
	 **/
	public function count_company_returns_expected_total()
	{
		$owner = User::factory()->create(['type' => 'company']);
		// three subsidiary companies
		User::factory()->count(3)->create(['type' => 'company', 'created_by' => $owner->id]);
		// a non-company user should not be counted
		User::factory()->create(['type' => 'user', 'created_by' => $owner->id]);

		$this->assertSame(3, $owner->countCompany());
	}

	/**
	 ** @test
	 **
	 ** getPlan / currentPlan relationships resolve a Plan model.
	 **/
	public function plan_relationship_helpers_return_plan_instance()
	{
		$plan = Plan::factory()->create();
		$user = User::factory()->create(['plan' => $plan->id]);

		// eager relationship via method
		$this->assertTrue($plan->is($user?->getPlan()->first()));
		// inverse helper
		$this->assertTrue($plan->is($user?->currentPlan()->first()));
	}

	/**
	 ** Factory helper to bang out a new record with minimal data.
	 **/
	private function quickUser(string $type, int $ownerId): User
	{
		return User::factory()->create([
			'type'       => $type,
			'created_by' => $ownerId,
			'is_active'  => 1,
		]);
	}
}
