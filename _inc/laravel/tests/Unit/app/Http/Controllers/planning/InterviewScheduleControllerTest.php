<?php

namespace Tests\Feature;

use App\Models\{InterviewSchedule, JobApplication, JobStage, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Gate, URL};
use Tests\TestCase;

class InterviewScheduleControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $company;
	private User $employee;
	private JobApplication $candidate;

	protected function setUp(): void
	{
		parent::setUp();

		// allow all permissions by default
		Gate::before(fn () => true);

		// macro so creatorId() returns own id
		User::macro(
			'creatorId',
			/** 
			 * @this \App\Models\User 
			 * @return int|string
			 **/
			function (): int|string {
				/** @var \App\Models\User $this */
				return $this->id;
			}
		);

		$this->company  = User::factory()->create(['type' => 'company']);
		$this->employee = User::factory()->create(['type' => 'employee', 'created_by' => $this->company->creatorId()]);
		$this->candidate = JobApplication::factory()->create(['created_by' => $this->company->creatorId()]);

		$this->actingAs($this->company);
	}

	/**
	 ** @test
	 **
	 ** index_lists_all_schedules_in_calendar_format:
	 **   - renders index view with schedules, arrSchedule JSON and transDate
	 **/
	public function index_lists_all_schedules_in_calendar_format()
	{
		InterviewSchedule::factory()->create([
			'candidate'   => $this->candidate->id,
			'employee'    => $this->employee->id,
			'date'        => '2025-01-01',
			'time'        => '10:00',
			'comment'     => 'Test',
			'created_by'  => $this->company->creatorId(),
		]);

		$resp = $this->get(route('interview-schedule.index'));

		$resp->assertOk()
			->assertViewIs('interviewSchedule.index')
			->assertViewHasAll(['arrSchedule', 'schedules', 'transDate']);

		// arrSchedule should be valid JSON array
		$json = $resp->viewData('arrSchedule');
		$this->assertStringContainsString('"title"', $json);
	}

	/**
	 ** @test
	 **
	 ** create_displays_form_with_employees_and_candidates:
	 **   - shows employee & candidate dropdown and settings
	 **/
	public function create_displays_form_with_employees_and_candidates()
	{
		$resp = $this->get(route('interview-schedule.create'));

		$resp->assertOk()
			->assertViewIs('interviewSchedule.create')
			->assertViewHasAll(['employees', 'candidates', 'settings']);
	}

	/**
	 ** @test
	 **
	 ** store_validates_and_creates_schedule:
	 **   - fails when missing mandatory fields
	 **   - succeeds and persists schedule record
	 **/
	public function store_validates_and_creates_schedule()
	{
		// validation failure
		$this->post(route('interview-schedule.store'), [])
			->assertStatus(302)
			->assertSessionHas('error');

		// success
		$payload = [
			'candidate'       => $this->candidate->id,
			'employee'        => $this->employee->id,
			'date'            => now()->toDateString(),
			'time'            => now()->format('H:i'),
			'comment'         => 'Interview',
			'synchronizeType' => '',
		];

		$this->post(route('interview-schedule.store'), $payload)
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('interview_schedules', [
			'candidate'  => $this->candidate->id,
			'employee'   => $this->employee->id,
			'comment'    => 'Interview',
		]);
	}

	/**
	 ** @test
	 **
	 ** show_displays_schedule_details:
	 **   - renders show view with stages list
	 **/
	public function show_displays_schedule_details()
	{
		$sched = InterviewSchedule::factory()->create([
			'candidate'  => $this->candidate->id,
			'employee'   => $this->employee->id,
			'date'       => now()->toDateString(),
			'time'       => now()->format('H:i'),
			'created_by' => $this->company->creatorId(),
		]);

		JobStage::factory()->count(2)->create(['created_by' => $this->company->creatorId()]);

		$resp = $this->get(route('interview-schedule.show', $sched));

		$resp->assertOk()
			->assertViewIs('interviewSchedule.show')
			->assertViewHasAll(['interviewSchedule', 'stages']);
	}

	/**
	 ** @test
	 **
	 ** edit_displays_form_with_existing_data:
	 **   - renders edit view with employees & candidates and existing schedule
	 **/
	public function edit_displays_form_with_existing_data()
	{
		$sched = InterviewSchedule::factory()->create([
			'candidate'  => $this->candidate->id,
			'employee'   => $this->employee->id,
			'date'       => now()->toDateString(),
			'time'       => now()->format('H:i'),
			'created_by' => $this->company->creatorId(),
		]);

		$resp = $this->get(route('interview-schedule.edit', $sched));

		$resp->assertOk()
			->assertViewIs('interviewSchedule.edit')
			->assertViewHasAll(['employees', 'candidates', 'interviewSchedule']);
	}

	/**
	 ** @test
	 **
	 ** update_validates_and_saves_changes:
	 **   - fails on missing fields
	 **   - updates schedule record on success
	 **/
	public function update_validates_and_saves_changes()
	{
		$sched = InterviewSchedule::factory()->create([
			'candidate'  => $this->candidate->id,
			'employee'   => $this->employee->id,
			'date'       => '2025-01-01',
			'time'       => '09:00',
			'comment'    => 'Old',
			'created_by' => $this->company->creatorId(),
		]);

		// validation fail
		$this->put(route('interview-schedule.update', $sched), ['candidate' => ''])
			->assertStatus(302)
			->assertSessionHas('error');

		// success
		$data = [
			'candidate' => $this->candidate->id,
			'employee'  => $this->employee->id,
			'date'      => now()->toDateString(),
			'time'      => now()->format('H:i'),
			'comment'   => 'Updated',
		];
		$this->put(route('interview-schedule.update', $sched), $data)
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseHas('interview_schedules', [
			'id'      => $sched->id,
			'comment' => 'Updated',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy_deletes_schedule:
	 **   - removes schedule and redirects back with success
	 **/
	public function destroy_deletes_schedule()
	{
		$sched = InterviewSchedule::factory()->create([
			'created_by' => $this->company->creatorId(),
		]);

		$this->delete(route('interview-schedule.destroy', $sched))
			->assertRedirect()
			->assertSessionHas('success');

		$this->assertDatabaseMissing('interview_schedules', ['id' => $sched->id]);
	}

	/**
	 ** @test
	 **
	 ** getInterviewData_returns_calendar_array:
	 **   - returns JSON array of events for default calendarType
	 **/
	public function getInterviewData_returns_calendar_array()
	{
		$sched = InterviewSchedule::factory()->create([
			'candidate'  => $this->candidate->id,
			'employee'   => $this->employee->id,
			'date'       => now()->toDateString(),
			'time'       => now()->format('H:i'),
			'comment'    => 'Test',
			'created_by' => $this->company->creatorId(),
		]);

		$resp = $this->getJson(route('interview-schedule.getData'));

		$resp->assertOk()
			->assertJsonFragment([
				'id'    => $sched->id,
				'title' => 'Test',
			]);
	}
}
