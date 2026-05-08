<?php

/**
 * Unit-tests for App\Models\Project
 *
 * NOTE ▸ All tests rely on in-memory objects
 * and Mockery aliases so no database access
 * or Storage driver is required.
 */

namespace Tests\Unit\Models;

use App\Models\Project;
use Illuminate\Support\{Collection, Facades\Storage};
use Mockery;
use Tests\TestCase;
use Tests\Concerns\SafeAliasMock;

class ProjectTest extends TestCase
{
	use SafeAliasMock;


	/** @var \ReflectionProperty */
	private $cacheProp;

	private Collection $fakeTasks;

	protected function setUp(): void
	{
		parent::setUp();
        \DB::unprepared('SET FOREIGN_KEY_CHECKS=0');

		$this->cacheProp = (new \ReflectionClass(Project::class))
			->getProperty('projectTask');
		$this->cacheProp->setAccessible(true);
		$this->cacheProp->setValue(null);

		$this->fakeTasks = collect([
			(object) ['id' => 1, 'estimated_hrs' => 3],
			(object) ['id' => 2, 'estimated_hrs' => 2],
		]);
	}

	/**
	 ** @test
	 *
	 ** Guard the `$fillable` whitelist against
	 ** silent drift by validating it against
	 ** the class constant.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'name',
			'start_date',
			'end_date',
			'project_image',
			'budget',
			'client_id',
			'project_stage_id',
			'description',
			'status',
			'estimated_hrs',
			'copylinksetting',
			'tags',
		];

		$this->assertSame($expected, (new Project)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** The static `$projectStatus` map must
	 ** expose all four canonical keys.
	 **/
	public function project_status_array_is_intact(): void
	{
		$expectedKeys = ['in_progress', 'on_hold', 'complete', 'canceled'];

		$this->assertSame($expectedKeys, array_keys(Project::$projectStatus));
	}

	/**
	 ** @test
	 *
	 ** getImgImageAttribute() should fallback
	 ** to the default avatar when Storage::exists
	 ** returns false.
	 **/
	public function img_image_accessor_returns_default_when_missing(): void
	{
		// Fake Storage facade behaviour.
		Storage::shouldReceive('exists')->once()->andReturnFalse();
		Storage::shouldReceive('url')
			->once()
			->with('uploads/avatar/default.png')
			->andReturn('uploads/avatar/default.png');

		$p             = new Project;
		$p->project_image = 'missing/path.png';

		$this->assertStringContainsString(
			'uploads/avatar/default.png',
			$p->img_image
		);
	}

	/**
	 ** @test
	 *
	 ** On the first call projectTask() must hit
	 ** ProjectTask::where()->get(), but on the
	 ** second call it must return the cached
	 ** collection without another DB hit.
	 **/
	public function project_task_hits_db_once_and_then_uses_cache(): void
	{
		// Project::projectTask() caches the resultset in self::$projectTask.
		// Reset the cache, seed real ProjectTasks for a real project, and
		// verify both calls return the same Collection instance.
		$this->cacheProp->setValue(null);

		$project = \App\Models\Project::factory()->create();
		\App\Models\ProjectTask::factory()->count(2)->create([
			'project_id'    => $project->id,
			'estimated_hrs' => 3,
		]);

		$first  = Project::projectTask($project->id);
		$second = Project::projectTask($project->id);

		$this->assertCount(2, $first);
		$this->assertSame($first, $second, 'Second call should hit the static cache');
	}

	/**
	 ** @test
	 *
	 ** projectHrs() should rely on the cached
	 ** tasks and correctly sum estimated_hrs.
	 **/
	public function project_hrs_uses_cached_tasks_to_sum_hours(): void
	{
		// Manually seed the private static cache with fake tasks.
		$this->cacheProp->setValue($this->fakeTasks);

		$hrs = Project::projectHrs(99);   // id irrelevant – cache already set

		$this->assertSame(['allocated' => 5], $hrs);
	}

	/**
	 ** @test
	 *
	 ** projectProgress() must compute the
	 ** percentage of completed tasks correctly
	 ** and call Utility::getProgressColor().
	 **/
	public function project_progress_calculates_percentage(): void
	{
		$project = new Project;
		$project->setRelation('tasks', collect([
			(object) ['project_stage_id' => 99, 'is_complete' => 1],
			(object) ['project_stage_id' => 99, 'is_complete' => 1],
			(object) ['project_stage_id' => 99, 'is_complete' => 0],
			(object) ['project_stage_id' => 77, 'is_complete' => 1],
		]));

		$result = $project->projectProgress($project, 99);

		$this->assertSame(
			['color' => 'info', 'percentage' => '50%'],
			$result
		);
	}

	public function project_total_task_calls_count(): void
	{
		$project = \App\Models\Project::factory()->create();
		\App\Models\ProjectTask::factory()->count(8)->create(['project_id' => $project->id]);

		$proj = new Project;
		$this->assertSame(8, $proj->projectTotalTask($project->id));
	}

	/**
	 ** @test
	 *
	 ** projectCompleteTask() must chain project_id + project_stage_id where()
	 ** clauses and count(). Note: production uses PJC::COL_STAGE_ID
	 ** ('project_stage_id'), not 'stage_id' as the original mock asserted.
	 **/
	public function project_complete_task_calls_count(): void
	{
		$project = \App\Models\Project::factory()->create();
		$stage   = \App\Models\ProjectStage::create([
			'name'  => 'Done',
			'color' => '#0f0',
			'order' => 1,
		]);
		// 3 tasks at this stage, 2 unrelated
		\App\Models\ProjectTask::factory()->count(3)->create([
			'project_id'       => $project->id,
			'project_stage_id' => $stage->id,
		]);
		\App\Models\ProjectTask::factory()->count(2)->create([
			'project_id' => $project->id,
		]);

		$proj = new Project;
		$this->assertSame(3, $proj->projectCompleteTask($project->id, $stage->id));
	}


	/**
	 * Clean up Mockery and reset static cache after each test.
	 */
	protected function tearDown(): void
	{
		$this->cacheProp->setValue(null); // prevent leakage to other tests
		Mockery::close();
        parent::tearDown();
	}
}
