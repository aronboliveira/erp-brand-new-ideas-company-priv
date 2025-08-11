<?php

namespace Tests\Unit\Http\Controllers;

use App\Models\{
	Bill,
	BillProduct,
	BillPayment,
	Company,
	Customer,
	Employee,
	ProductService,
	ProductServiceCategory,
	ProductServiceUnit,
	User,
	Vendor,
};
use App\Http\Controllers\ExpenseController;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Crypt, DB, Gate, Log, Storage};
use Tests\TestCase;

final class ExpenseControllerTest extends TestCase
{
	use RefreshDatabase;

	protected User $user;
	private Employee $employee;
	private User $company;
	private Bill $bill;

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->create();
		$this->actingAs($this->user);
		$this->employee = Employee::factory()->create([
			'created_by' => $this->user->creatorId(),
		]);
		$this->company = \App\Models\User::factory()->create(['type' => 'company']);
		$this->actingAs($this->company);
		Log::spy();
		Storage::fake('local');
		DB::table('settings')->insert([
			['created_by' => 1, 'name' => 'company_logo_dark', 'value' => 'dark.png'],
			['created_by' => 1, 'name' => 'bill_template',       'value' => 'default'],
			['created_by' => 1, 'name' => 'bill_color',          'value' => 'ff0000'],
		]);
		$unit = ProductServiceUnit::factory()->create(['name' => 'pcs']);
		$prod = ProductService::factory()->create([
			'unit_id'        => $unit->id,
			'tax_id'         => null,
			'purchase_price' => 50,
		]);

		$this->bill = Bill::factory()->create(['created_by' => $this->company->creatorId()]);
		BillProduct::factory()->create([
			'bill_id'    => $this->bill->id,
			'product_id' => $prod->id,
			'quantity'   => 2,
			'price'      => 50,
			'discount'   => 5,
			'tax'        => 10,
		]);
	}

	/**
	 ** @test
	 **
	 ** The index route should render the expense.index view successfully.
	 **/
	public function test_index_renders_view(): void
	{
		$response = $this->get(route('expense.index'));
		$response
			->assertStatus(200)
			->assertViewIs('expense.index');
	}

	/**
	 ** @test
	 **
	 ** The create route with a reference ID should render the expense.create form.
	 **/
	public function test_create_renders_form(): void
	{
		$response = $this->get(route('expense.create', ['refId' => 1]));
		$response
			->assertStatus(200)
			->assertViewIs('expense.create');
	}

	/**
	 ** @test
	 **
	 ** Posting valid expense data should create a new bill, bill products,
	 ** and a payment record, then redirect to index.
	 **/
	public function test_store_creates_expense(): void
	{
		$vendor   = Vendor::factory()->create(['created_by' => $this->user->creatorId()]);
		$account  = \App\Models\ChartOfAccount::factory()->create(['created_by' => $this->user->creatorId()]);
		$item     = ProductService::factory()->create(['created_by' => $this->user->creatorId()]);
		$category = ProductServiceCategory::factory()->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'expense',
		]);

		$response = $this->post(route('expense.store'), [
			'type'          => 'vendor',
			'vendor_id'     => $vendor->id,
			'payment_date'  => now()->toDateString(),
			'category_id'   => $category->id,
			'account_id'    => $account->id,
			'totalAmount'   => 500,
			'items'         => [[
				'item'        => $item->id,
				'quantity'    => 2,
				'price'       => 250,
				'tax'         => 0,
				'discount'    => 0,
				'description' => 'Test product',
			]],
		]);

		$response->assertRedirect(route('expense.index'));
		$this->assertDatabaseHas('bills', [
			'type'       => 'Expense',
			'vendor_id'  => $vendor->id,
		]);
		$this->assertDatabaseHas('bill_products', [
			'product_id' => $item->id,
		]);
		$this->assertDatabaseHas('bill_payments', [
			'amount' => 500,
		]);
	}

	/**
	 ** @test
	 **
	 ** The show route should decrypt the bill ID and render the expense.view.
	 **/
	public function test_show_displays_expense(): void
	{
		$bill = Bill::factory()->create([
			'type'        => 'Expense',
			'created_by'  => $this->user->creatorId(),
		]);
		$encId = Crypt::encryptString($bill->id);

		$response = $this->get(route('expense.show', ['encId' => $encId]));
		$response
			->assertStatus(200)
			->assertViewIs('expense.view');
	}

	/**
	 ** @test
	 **
	 ** The edit route should decrypt the bill ID and render the expense.edit form.
	 **/
	public function test_edit_renders_edit_form(): void
	{
		$bill = Bill::factory()->create([
			'type'        => 'Expense',
			'created_by'  => $this->user->creatorId(),
		]);
		$encId = Crypt::encryptString($bill->id);

		$response = $this->get(route('expense.edit', ['encId' => $encId]));
		$response
			->assertStatus(200)
			->assertViewIs('expense.edit');
	}

	/**
	 ** @test
	 **
	 ** Sending valid update data should modify the bill’s category and redirect.
	 **/
	public function test_update_modifies_expense(): void
	{
		$bill    = Bill::factory()->create([
			'type'        => 'Expense',
			'created_by'  => $this->user->creatorId(),
		]);
		$category = ProductServiceCategory::factory()->create([
			'created_by' => $this->user->creatorId(),
			'type'       => 'expense',
		]);

		$response = $this->put(route('expense.update', $bill->id), [
			'type'        => 'vendor',
			'vendor_id'   => $bill->vendor_id,
			'bill_date'   => now()->toDateString(),
			'category_id' => $category->id,
		]);

		$response->assertRedirect(route('expense.index'));
		$this->assertDatabaseHas('bills', [
			'id'          => $bill->id,
			'category_id' => $category->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** The destroy route should delete the bill record and redirect.
	 **/
	public function test_destroy_deletes_expense(): void
	{
		$bill = Bill::factory()->create([
			'type'        => 'Expense',
			'created_by'  => $this->user->creatorId(),
		]);

		$response = $this->delete(route('expense.destroy', $bill->id));
		$response->assertRedirect(route('expense.index'));
		$this->assertDatabaseMissing('bills', [
			'id' => $bill->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Deleting a single bill product via its route should remove it
	 ** and redirect back.
	 **/
	public function test_product_destroy_deletes_bill_product(): void
	{
		$bp = BillProduct::factory()->create();
		$response = $this->delete(route('expense.product.destroy'), [
			'id'     => $bp->id,
			'amount' => 50,
		]);

		$response->assertRedirect();
		$this->assertDatabaseMissing('bill_products', [
			'id' => $bp->id,
		]);
	}


	/**
	 ** @test
	 **
	 ** It should show the employee detail view
	 ** when the employee exists and permission is granted.
	 **/
	public function employee_displays_detail_for_existing_employee()
	{
		$response = $this->get(route('expense.employee', ['id' => $this->employee->id]));

		$response->assertStatus(200)
			->assertViewIs('expense.employee_detail')
			->assertViewHas('emp', fn ($emp) => $emp->id === $this->employee->id);
	}

	/**
	 ** @test
	 **
	 ** It should redirect back with an error
	 ** when the given employee ID does not exist.
	 **/
	public function employee_redirects_back_with_error_if_employee_not_found()
	{
		$invalidId = 9999;

		$response = $this->get(route('expense.employee', ['id' => $invalidId]));

		$response->assertRedirect()
			->assertSessionHas('error', __('Employee not found.'));
	}

	/**
	 ** @test
	 **
	 ** It should return 403 Forbidden
	 ** when the user lacks the 'manage bill' permission.
	 **/
	public function employee_denies_access_without_manage_bill_permission()
	{
		Gate::before(fn () => false);

		$response = $this->get(route('expense.employee', ['id' => $this->employee->id]));

		$response->assertStatus(403);
	}

	/** @test
	 *
	 * Employee detail shows view when employee exists and permission granted
	 **/
	public function employee_displays_view_if_employee_found()
	{
		$emp = Employee::factory()->create(['created_by' => $this->company->creatorId()]);

		$response = $this->get(action([ExpenseController::class, 'employee'], ['id' => $emp->id]));

		$response->assertStatus(200)
			->assertViewIs('expense.employee_detail')
			->assertViewHas('employee', fn ($e) => $e->id === $emp->id);
	}

	/** @test
	 *
	 * Employee detail redirects back with error when not found
	 **/
	public function employee_redirects_back_with_error_if_not_found()
	{
		$response = $this->get(action([ExpenseController::class, 'employee'], ['id' => 999]));

		$response->assertRedirect()
			->assertSessionHas('error', 'Employee not found.');
	}

	/** @test
	 *
	 * Vendor detail shows view when vendor exists and permission granted
	 **/
	public function vendor_displays_view_if_vendor_found()
	{
		$vendor = Vendor::factory()->create(['created_by' => $this->company->creatorId()]);

		$response = $this->get(action([ExpenseController::class, 'vendor'], ['id' => $vendor->id]));

		$response->assertStatus(200)
			->assertViewIs('expense.vendor_detail')
			->assertViewHas('vendor', fn ($v) => $v->id === $vendor->id);
	}

	/** @test
	 *
	 * Vendor detail redirects back with error when not found
	 **/
	public function vendor_redirects_back_with_error_if_not_found()
	{
		$response = $this->get(action([ExpenseController::class, 'vendor'], ['id' => 999]));

		$response->assertRedirect()
			->assertSessionHas('error', 'Vendor not found.');
	}

	/** @test
	 *
	 * Customer detail shows view when customer exists and permission granted
	 **/
	public function customer_displays_view_if_customer_found()
	{
		$customer = Customer::factory()->create(['created_by' => $this->company->creatorId()]);

		$response = $this->get(action([ExpenseController::class, 'customer'], ['id' => $customer->id]));

		$response->assertStatus(200)
			->assertViewIs('expense.customer_detail')
			->assertViewHas('customer', fn ($c) => $c->id === $customer->id);
	}

	/** @test
	 *
	 * Customer detail redirects back with error when not found
	 **/
	public function customer_redirects_back_with_error_if_not_found()
	{
		$response = $this->get(action([ExpenseController::class, 'customer'], ['id' => 999]));

		$response->assertRedirect()
			->assertSessionHas('error', 'Customer not found.');
	}

	/** @test
	 *
	 * Items returns JSON of BillProduct when found
	 **/
	public function items_returns_json_of_bill_product()
	{
		$bp = BillProduct::factory()->create([
			'bill_id'    => 123,
			'product_id' => 456,
			'created_by' => $this->company->creatorId(),
		]);

		$response = $this->getJson(action([ExpenseController::class, 'items']), [
			'bill_id'    => 123,
			'product_id' => 456,
		]);

		$response->assertStatus(200)
			->assertJson([
				'bill_id'    => 123,
				'product_id' => 456,
			]);
	}

	/** @test
	 *
	 * Items returns null JSON when none found
	 **/
	public function items_returns_null_json_when_no_record()
	{
		$response = $this->getJson(action([ExpenseController::class, 'items']), [
			'bill_id'    => 999,
			'product_id' => 888,
		]);

		$response->assertStatus(200)
			->assertExactJson([]);
	}

	/** @test
	 *
	 * All endpoints deny access when permission missing
	 **/
	public function endpoints_deny_access_if_no_manage_bill_permission()
	{
		Gate::before(fn ($u, $perm) => $perm === 'manage bill' ? false : true);

		$urls = [
			action([ExpenseController::class, 'employee'], ['id' => 1]),
			action([ExpenseController::class, 'vendor'],   ['id' => 1]),
			action([ExpenseController::class, 'customer'], ['id' => 1]),
			action([ExpenseController::class, 'items']),
		];

		foreach ($urls as $url) {
			$resp = str_contains($url, 'items')
				? $this->getJson($url, ['bill_id' => 1, 'product_id' => 1])
				: $this->get($url);

			$resp->assertStatus(302);
		}
	}

	/** @test
	 **
	 ** Guest users are redirected to login
	 **/
	public function guest_cannot_access_product_endpoint()
	{
		$response = $this->postJson(route('expense.product'));
		$response->assertStatus(302);
	}

	/** @test
	 **
	 ** Validation fails when product_id is missing or invalid
	 **/
	public function product_validation_fails_for_missing_or_bad_product_id()
	{
		$this->actingAs($this->user)
			->from('/previous')
			->post(route('expense.product'), [])
			->assertRedirect('/previous')
			->assertSessionHasErrors('product_id');

		// also test non-integer
		$this->actingAs($this->user)
			->from('/previous')
			->post(route('expense.product'), ['product_id' => 'abc'])
			->assertRedirect('/previous')
			->assertSessionHasErrors('product_id');
	}

	/** @test
	 **
	 ** Returns 404 JSON if product not found
	 **/
	public function product_not_found_returns_404_json()
	{
		$this->actingAs($this->user)
			->postJson(route('expense.product'), ['product_id' => 999])
			->assertStatus(Response::HTTP_NOT_FOUND)
			->assertJson(['error' => __('Product not found.')]);
	}

	/** @test
	 **
	 ** Returns correct JSON payload on success
	 **/
	public function product_returns_expected_json_on_success()
	{
		// set up a unit and a product service
		$unit = ProductServiceUnit::factory()->create(['name' => 'pcs']);
		$product = ProductService::factory()->create([
			'unit_id'        => $unit->id,
			'purchase_price' => 42.50,
			// assume tax_id null so taxRate/taxes = 0
		]);

		$resp = $this->actingAs($this->user)
			->postJson(route('expense.product'), [
				'product_id' => $product->id,
			]);

		$resp->assertStatus(Response::HTTP_OK)
			->assertJsonStructure([
				'product'     => ['id', 'unit_id', 'purchase_price',/* ... */],
				'unit',
				'taxRate',
				'taxes',
				'totalAmount',
			])
			->assertJson([
				'unit'        => 'pcs',
				'taxRate'     => 0,
				'taxes'       => 0,
				'totalAmount' => 42.50 * 1,
			]);
	}

	/**
	 ** @test
	 *
	 ** A company user can view their own bill—decrypting the ID,
	 ** calculating totals & taxes, and rendering the chosen template.
	 **/
	public function company_user_sees_bill_template_with_correct_totals()
	{
		$enc = Crypt::encryptString($this->bill->id);

		$response = $this->get(route('expense.expense', $enc));

		$response->assertOk()
			->assertViewIs('bill.templates.default')
			->assertViewHasAll(['expense', 'img', 'fontColor']);

		$expense = $response->viewData('expense');

		// Qty = 2, Rate = 50, Discount = 5,
		// TaxPrice = (price - discount) * qty * tax% = (50-5)*2*0.1 = 9
		$this->assertEquals(2,   $expense->totalQuantity);
		$this->assertEquals(50,  $expense->totalRate);
		$this->assertEquals(5,   $expense->totalDiscount);
		$this->assertEqualsWithDelta(9, $expense->totalTaxPrice, 0.1);
	}

	/**
	 ** @test
	 *
	 ** Accessing another company’s bill returns 403 Forbidden.
	 **/
	public function non_owner_gets_403_forbidden()
	{
		$other = User::factory()->create(['type' => 'company']);
		$this->actingAs($other);

		$enc = Crypt::encryptString($this->bill->id);
		$response = $this->get(route('expense.expense', $enc));

		$response->assertStatus(403);
	}

	/**
	 ** @test
	 *
	 ** Providing an invalid encrypted ID redirects back with an error message.
	 **/
	public function invalid_encrypted_id_redirects_with_error()
	{
		$response = $this->get(route('expense.expense', 'bad-enc'));

		$response->assertRedirect()
			->assertSessionHas('error', __('Bill not found.'));
	}

	/**
	 ** @test
	 *
	 ** Guests (unauthenticated users) are redirected to the login page.
	 **/
	public function guest_is_redirected_to_login()
	{
		auth()->logout();

		$enc = Crypt::encryptString($this->bill->id);
		$response = $this->get(route('expense.expense', $enc));

		$response->assertRedirect(); // defaults to login
	}
}
