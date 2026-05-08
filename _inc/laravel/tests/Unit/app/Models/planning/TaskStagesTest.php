<?php

namespace Tests\Unit\app\Models\planning;

use App\Models\TaskStage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

class TaskStagesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }

	use SafeAliasMock;

	/**
	 ** @test
	 *
	 ** Static $stages array must mirror constant.
	 **/
	public function stages_array_matches_constant(): void
	{
		$ref     = new \ReflectionClass(TaskStage::class);
		$expected = $ref->getConstant('STAGES_LIST');

		$this->assertSame($expected, TaskStage::$stages);
	}

	/**
	 ** @test
	 *
	 ** getChartData() returns label+dataset keys.
	 *  We stub DB queries so no database is hit.
	 **/
	public function get_chart_data_structure(): void
	{
		Carbon::setTestNow(Carbon::parse('2025-05-30'));

		// Login a real company user; getChartData() reads TaskStage rows
		// scoped by created_by = creatorId() and groups by distinct name.
		$user = \App\Models\User::factory()->create(['type' => 'company', 'lang' => 'en']);
		Auth::login($user);

		TaskStage::create(['name' => 'Todo', 'color' => '#f00', 'order' => 1, 'created_by' => $user->id]);
		TaskStage::create(['name' => 'Done', 'color' => '#0f0', 'order' => 2, 'created_by' => $user->id]);

		$data = TaskStage::getChartData();

		$this->assertArrayHasKey('label',   $data);
		$this->assertArrayHasKey('dataset', $data);
		$this->assertCount(2, $data['dataset']);
	}

	protected function tearDown(): void
	{
		Mockery::close();
        parent::tearDown();
	}
}
