<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class HolidayControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();

		// By default grant all permissions
		Gate::before(fn () => true);

		// Macro so creatorId() returns the user's own ID
		User::macro('creatorId', function () {
			/** @var User $this */
			return $this->id;
		});

		$this->user = User::factory()->create();
	}

	/**
	 ** @test
	 **
	 ** Index requires manage permission and returns 403 when unauthorized.
	 **/
	public function index_requires_manage_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->user)
			->get(route('holiday.index'))
			->assertStatus(403);

		Gate::before(fn () => true);
	}

	/**
	 ** @test
	 **
	 ** Index lists all holidays for the user, and filters by start/end dates.
	 **/
	public function index_filters_by_dates()
	{
		$today = now()->startOfDay();
		Holiday::factory()->create([
			'date'       => $today->copy()->subDay()->toDateString(),
			'end_date'   => $today->toDateString(),
			'occasion'   => 'Past',
			'created_by' => $this->user->creatorId(),
		]);
		Holiday::factory()->create([
			'date'       => $today->toDateString(),
			'end_date'   => $today->toDateString(),
			'occasion'   => 'Today',
			'created_by' => $this->user->creatorId(),
		]);
		Holiday::factory()->create([
			'date'       => $today->copy()->addDay()->toDateString(),
			'end_date'   => $today->toDateString(),
			'occasion'   => 'Future',
			'created_by' => $this->user->creatorId(),
		]);

		// no filters: all three
		$this->actingAs($this->user)
			->get(route('holiday.index'))
			->assertOk()
			->assertViewHas('holidays', fn ($h) => $h->count() === 3);

		// filter start_date = today => Today & Future
		$this->actingAs($this->user)
			->get(route('holiday.index', ['start_date' => $today->toDateString()]))
			->assertOk()
			->assertViewHas('holidays', fn ($h) => $h->pluck('occasion')->all() === ['Today', 'Future']);

		// filter end_date = today => Past & Today
		$this->actingAs($this->user)
			->get(route('holiday.index', ['end_date' => $today->toDateString()]))
			->assertOk()
			->assertViewHas('holidays', fn ($h) => $h->pluck('occasion')->all() === ['Past', 'Today']);
	}

	/**
	 ** @test
	 **
	 ** Create requires create permission and returns 403 when unauthorized.
	 **/
	public function create_requires_permission()
	{
		Gate::before(fn () => false);

		$this->actingAs($this->user)
			->get(route('holiday.create'))
			->assertStatus(403);

		Gate::before(fn () => true);
	}

	/**
	 ** @test
	 **
	 ** Create displays the form with settings when authorized.
	 **/
	public function create_displays_form()
	{
		// stub Utility::settings() if needed; otherwise just ensure view loads
		$this->actingAs($this->user)
			->get(route('holiday.create'))
			->assertOk()
			->assertViewIs('holiday.create')
			->assertViewHas('settings');
	}

	/**
	 ** @test
	 **
	 ** Store validates input and creates a new holiday, then redirects with success.
	 **/
	public function store_validates_and_creates_holiday()
	{
		// missing fields => validation errors
		$this->actingAs($this->user)
			->post(route('holiday.store'), [])
			->assertSessionHasErrors(['date', 'occasion']);

		// valid payload
		$payload = [
			'date'             => '2025-01-01',
			'end_date'         => '2025-01-02',
			'occasion'         => 'New Year Fest',
			'synchronize_type' => null,
		];

		$this->actingAs($this->user)
			->post(route('holiday.store'), $payload)
			->assertRedirect(route('holiday.index'))
			->assertSessionHas('success', __('Holiday successfully created.'));

		$this->assertDatabaseHas('holidays', [
			'occasion'   => 'New Year Fest',
			'created_by' => $this->user->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Show requires show permission and ownership, then displays the holiday.
	 **/
	public function show_requires_permission_and_owner()
	{
		$holiday = Holiday::factory()->create(['created_by' => $this->user->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->user)
			->get(route('holiday.show', $holiday))
			->assertStatus(403);

		// reset to allow permission
		Gate::before(fn () => true);

		// wrong owner
		$other = User::factory()->create();
		$this->actingAs($other)
			->get(route('holiday.show', $holiday))
			->assertStatus(403);

		// correct owner
		$this->actingAs($this->user)
			->get(route('holiday.show', $holiday))
			->assertOk()
			->assertViewIs('holiday.show')
			->assertViewHas('holiday', fn ($h) => $h->id === $holiday->id);
	}

	/**
	 ** @test
	 **
	 ** Edit requires edit permission and ownership, then displays the form.
	 **/
	public function edit_requires_permission_and_owner()
	{
		$holiday = Holiday::factory()->create(['created_by' => $this->user->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->user)
			->get(route('holiday.edit', $holiday))
			->assertStatus(403);

		// reset to allow permission
		Gate::before(fn () => true);

		// wrong owner
		$other = User::factory()->create();
		$this->actingAs($other)
			->get(route('holiday.edit', $holiday))
			->assertStatus(403);

		// correct owner
		$this->actingAs($this->user)
			->get(route('holiday.edit', $holiday))
			->assertOk()
			->assertViewIs('holiday.edit')
			->assertViewHas('holiday', fn ($h) => $h->id === $holiday->id);
	}

	/**
	 ** @test
	 **
	 ** Update validates input and updates the holiday, then redirects with success.
	 **/
	public function update_validates_and_updates_holiday()
	{
		$holiday = Holiday::factory()->create([
			'date'       => '2025-02-01',
			'occasion'   => 'Old Occ',
			'created_by' => $this->user->creatorId(),
		]);

		// validation errors
		$this->actingAs($this->user)
			->put(route('holiday.update', $holiday), ['date' => '', 'occasion' => ''])
			->assertSessionHasErrors(['date', 'occasion']);

		// successful update
		$data = [
			'date'     => '2025-03-01',
			'occasion' => 'Updated Occ',
			'end_date' => null,
		];

		$this->actingAs($this->user)
			->put(route('holiday.update', $holiday), $data)
			->assertRedirect(route('holiday.index'))
			->assertSessionHas('success', __('Holiday successfully updated.'));

		$this->assertDatabaseHas('holidays', [
			'id'         => $holiday->id,
			'occasion'   => 'Updated Occ',
		]);
	}

	/**
	 ** @test
	 **
	 ** Destroy requires delete permission and ownership, then deletes the holiday.
	 **/
	public function destroy_requires_permission_and_owner_and_deletes()
	{
		$holiday = Holiday::factory()->create(['created_by' => $this->user->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->user)
			->delete(route('holiday.destroy', $holiday))
			->assertStatus(403);

		// reset to allow permission
		Gate::before(fn () => true);

		// wrong owner
		$other = User::factory()->create();
		$this->actingAs($other)
			->delete(route('holiday.destroy', $holiday))
			->assertStatus(403);

		// successful delete
		$this->actingAs($this->user)
			->delete(route('holiday.destroy', $holiday))
			->assertRedirect(route('holiday.index'))
			->assertSessionHas('success', __('Holiday successfully deleted.'));

		$this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
	}

	/**
	 ** @test
	 **
	 ** Calendar view requires manage permission and displays calendar data.
	 **/
	public function calendar_requires_manage_permission_and_displays_data()
	{
		$holiday = Holiday::factory()->create(['created_by' => $this->user->creatorId()]);

		// no permission
		Gate::before(fn () => false);
		$this->actingAs($this->user)
			->get(route('holiday.calendar'))
			->assertStatus(403);

		// allow and view
		Gate::before(fn () => true);
		$this->actingAs($this->user)
			->get(route('holiday.calendar'))
			->assertOk()
			->assertViewIs('holiday.calendar')
			->assertViewHasAll(['arrHolidays', 'transDate', 'holidays']);
	}

	/**
	 ** @test
	 **
	 ** getHolidayData returns an array of holiday JSON objects when calendar_type != 'google_calendar'.
	 **/
	public function get_holiday_data_returns_array()
	{
		Holiday::factory()->count(2)->create(['created_by' => $this->user->creatorId()]);

		$resp = $this->actingAs($this->user)
			->getJson(route('holiday.getHolidayData', ['calendar_type' => 'custom']));

		$resp->assertOk()
			->assertJsonCount(2)
			->assertJsonStructure([['id', 'title', 'start', 'end', 'url']]);
	}
}
