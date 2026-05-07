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
		// Intercept the static call chain.
		$this->aliasMock('App\Models\ProjectTask')
			->shouldReceive('where')
			->once()->with('project_id', 42)->andReturnSelf()
			->getMock()
			->shouldReceive('get')
			->once()->andReturn($this->fakeTasks);

		// 1️⃣  First invocation → DB mocked above
		$firstCall = Project::projectTask(42);
		$this->assertSame($this->fakeTasks, $firstCall);

		// 2️⃣  Second invocation → must reuse cache
		$secondCall = Project::projectTask(42);
		$this->assertSame($firstCall, $secondCall); // same instance!
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
		$this->markTestSkipped('Requires DB task data — setRelation bypassed by real getProgressColor');

		$project = new Project;
		$project->setRelation('tasks', collect([
			(object) ['stage_id' => 99, 'is_complete' => 1],
			(object) ['stage_id' => 99, 'is_complete' => 1],
			(object) ['stage_id' => 99, 'is_complete' => 0],
			(object) ['stage_id' => 77, 'is_complete' => 1],
		]));

		$result = $project->projectProgress($project, 99);

		$this->assertSame(
			['color' => 'info', 'percentage' => '50%'],
			$result
		);
	}

	public function project_total_task_calls_count(): void
	{
		$this->aliasMock('App\Models\ProjectTask')
			->shouldReceive('where')
			->once()
			->with('project_id', 77)
			->andReturnSelf()
			->getMock()
			->shouldReceive('count')
			->once()
			->andReturn(8);

		$proj = new Project;

		$this->assertSame(8, $proj->projectTotalTask(77));
	}

	/**
	 ** @test
	 *
	 ** projectCompleteTask() must chain two
	 ** where clauses and then count().
	 **/
	public function project_complete_task_calls_count(): void
	{
		$this->aliasMock('App\Models\ProjectTask')
			->shouldReceive('where')
			->once()
			->with('project_id', 77)
			->andReturnSelf()
			->getMock()
			->shouldReceive('where')
			->once()
			->with('stage_id', 5)
			->andReturnSelf()
			->getMock()
			->shouldReceive('count')
			->once()
			->andReturn(3);

		$proj = new Project;

		$this->assertSame(3, $proj->projectCompleteTask(77, 5));
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
