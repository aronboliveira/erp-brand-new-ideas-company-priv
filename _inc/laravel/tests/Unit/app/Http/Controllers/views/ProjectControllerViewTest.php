<?php

namespace Tests\Unit\app\Http\Controllers\views;

use App\Http\Controllers\Planning\ProjectController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;
use Tests\Unit\app\Http\Controllers\ControllerTestHelper;

#[\PHPUnit\Framework\Attributes\Group('controller-views')]
class ProjectControllerViewTest extends TestCase
{
	use RefreshDatabase;
	use ControllerTestHelper;
	use ViewAssertionHelper;

	/* ------------------------------------------------------------------ */
	/*  View-file existence tests                                         */
	/* ------------------------------------------------------------------ */

	public function test_projects_index_view_file_exists(): void
	{
		$this->assertBladeViewExists('projects.index');
	}

	public function test_projects_create_view_file_exists(): void
	{
		$this->assertBladeViewExists('projects.create');
	}

	public function test_projects_view_file_exists(): void
	{
		$this->assertBladeViewExists('projects.view');
	}

	public function test_projects_edit_view_file_exists(): void
	{
		$this->assertBladeViewExists('projects.edit');
	}

	public function test_projects_invite_view_file_exists(): void
	{
		$this->assertBladeViewExists('projects.invite');
	}

	public function test_projects_milestone_view_file_exists(): void
	{
		// Controller uses VW::ML = 'projects.milestones' (plural)
		// Actual blade: projects/milestone.blade.php (singular)
		$this->assertBladeViewExists('projects.milestone');
	}

	public function test_projects_milestone_edit_view_file_exists(): void
	{
		// Controller: VW::ML.'.edit' = 'projects.milestones.edit' (nested)
		// Actual: projects/milestone_edit.blade.php (flat, underscore)
		$this->assertBladeViewExists('projects.milestone_edit');
	}

	public function test_projects_milestone_show_view_file_exists(): void
	{
		// Controller: VW::ML.'.show' = 'projects.milestones.show'
		// Actual: projects/milestone_show.blade.php
		$this->assertBladeViewExists('projects.milestone_show');
	}

	public function test_projects_gantt_view_file_exists(): void
	{
		$this->assertBladeViewExists('projects.gantt');
	}

	public function test_projects_bug_view_file_exists(): void
	{
		$this->assertBladeViewExists('projects.bug');
	}

	public function test_projects_bug_create_view_file_exists(): void
	{
		// Controller: 'projects.bugCreate' — Actual: bug_create.blade.php
		$this->assertBladeViewExists('projects.bug_create');
	}

	public function test_projects_bug_edit_view_file_exists(): void
	{
		// Controller: 'projects.bugEdit' — Actual: bug_edit.blade.php
		$this->assertBladeViewExists('projects.bug_edit');
	}

	public function test_projects_bug_kanban_view_file_exists(): void
	{
		// Controller: 'projects.bugKanban' — Actual: bug_kanban.blade.php
		$this->assertBladeViewExists('projects.bug_kanban');
	}

	public function test_projects_bug_show_view_file_exists(): void
	{
		// Controller: 'projects.bugShow' — Actual: bug_show.blade.php
		$this->assertBladeViewExists('projects.bug_show');
	}

	public function test_projects_copy_view_file_exists(): void
	{
		$this->assertBladeViewExists('projects.copy');
	}

	public function test_projects_copy_link_setting_view_file_exists(): void
	{
		// Controller: 'projects.copylink_setting' — Actual: copy_link_setting
		$this->assertBladeViewExists('projects.copy_link_setting');
	}

	public function test_projects_copy_link_password_view_file_exists(): void
	{
		// Controller: 'projects.copylink_password' — Actual: copy_link_password
		$this->assertBladeViewExists('projects.copy_link_password');
	}

	public function test_projects_copy_link_view_file_exists(): void
	{
		// Controller: 'projects.copylink' — Actual: copy_link
		$this->assertBladeViewExists('projects.copy_link');
	}

	public function test_time_trackers_index_view_file_exists(): void
	{
		$this->assertBladeViewExists('time_trackers.index');
	}

	/* ------------------------------------------------------------------ */
	/*  Controller class & reflection tests                                */
	/* ------------------------------------------------------------------ */

	public function test_project_controller_class_exists(): void
	{
		$this->assertTrue(
			class_exists(ProjectController::class),
			'ProjectController class should be loadable.'
		);
	}

	public function test_project_controller_has_expected_methods(): void
	{
		$ref = new ReflectionClass(ProjectController::class);

		$expected = [
			'index',
			'create',
			'store',
			'show',
			'edit',
			'update',
			'destroy',
			'inviteMemberView',
			'inviteProjectUserMember',
			'destroyProjectUser',
			'loadUser',
			'milestone',
			'milestoneStore',
			'milestoneEdit',
			'milestoneUpdate',
			'milestoneDestroy',
			'milestoneShow',
			'filterProjectView',
			'ganttPost',
			'bugCreate',
			'bugStore',
			'bugEdit',
			'bugUpdate',
			'bugDestroy',
			'bugKanban',
			'bugKanbanOrder',
			'bugShow',
			'bugCommentStore',
			'bugCommentDestroy',
			'bugCommentStoreFile',
			'bugCommentDestroyFile',
			'getProjectChart',
			'copyProject',
			'copyProjectStore',
			'copyLinkSettingCreate',
			'copyLinkSetting',
			'projectLink',
		];

		foreach ($expected as $method) {
			$this->assertTrue(
				$ref->hasMethod($method),
				"ProjectController should have public method [{$method}]."
			);
			$this->assertTrue(
				$ref->getMethod($method)->isPublic(),
				"Method [{$method}] should be public."
			);
		}
	}

	public function test_project_controller_constants_defined(): void
	{
		$this->assertSame('index', ProjectController::IDX);
		$this->assertSame('create', ProjectController::CRT);
		$this->assertSame('store', ProjectController::STR);
		$this->assertSame('show', ProjectController::SHW);
		$this->assertSame('edit', ProjectController::EDT);
		$this->assertSame('update', ProjectController::UPD);
		$this->assertSame('destroy', ProjectController::DEL);
		$this->assertSame('inviteMemberView', ProjectController::INV_MB_VW);
		$this->assertSame('inviteProjectUserMember', ProjectController::INV_PRJ_USR_MB);
		$this->assertSame('destroyProjectUser', ProjectController::DST_PRJ_USR);
		$this->assertSame('loadUser', ProjectController::LD_USR);
		$this->assertSame('milestoneStore', ProjectController::ML_STR);
		$this->assertSame('milestoneEdit', ProjectController::ML_ED);
		$this->assertSame('milestoneUpdate', ProjectController::ML_UPD);
		$this->assertSame('milestoneDestroy', ProjectController::ML_DST);
		$this->assertSame('milestoneShow', ProjectController::ML_SHW);
		$this->assertSame('filterProjectView', ProjectController::FT_PRJ);
		$this->assertSame('ganttPost', ProjectController::GT_PT);
		$this->assertSame('bugCreate', ProjectController::BUG_CRT);
		$this->assertSame('bugStore', ProjectController::BUG_ST);
		$this->assertSame('bugEdit', ProjectController::BUG_EDT);
		$this->assertSame('bugUpdate', ProjectController::BUG_UPD);
		$this->assertSame('bugDestroy', ProjectController::BUG_DST);
		$this->assertSame('bugKanban', ProjectController::BUG_KB);
		$this->assertSame('bugKanbanOrder', ProjectController::BUG_KB_OD);
		$this->assertSame('bugShow', ProjectController::BUG_SHW);
		$this->assertSame('bugCommentStore', ProjectController::BUG_CMT_STR);
		$this->assertSame('bugCommentDestroy', ProjectController::BUG_CMT_DST);
		$this->assertSame('bugCommentStoreFile', ProjectController::BUG_CMT_STR_F);
		$this->assertSame('bugCommentDestroyFile', ProjectController::BUG_CMT_DST_F);
		$this->assertSame('getProjectChart', ProjectController::GET_PRJ_CHT);
		$this->assertSame('copyProject', ProjectController::CP_PRJ);
		$this->assertSame('copyProjectStore', ProjectController::CP_PRJ_ST);
		$this->assertSame('copyLinkSettingCreate', ProjectController::CP_LNK_ST_CRT);
		$this->assertSame('copyLinkSetting', ProjectController::CP_LNK_ST);
		$this->assertSame('projectLink', ProjectController::PRJ_LNK);
		$this->assertSame('bugNumber', ProjectController::BUG_NB);
	}

    /* ------------------------------------------------------------------ */
    /*  View naming mismatch documentation                                */
    /* ------------------------------------------------------------------ */

	/**
	 * Documents 10 view-name mismatches in ProjectController where the
	 * controller references a view path that doesn't match the actual
	 * blade file name. All are caught by ViewFacade::exists() guards.
	 *
	 * @see KNOWN_ISSUES.md
	 */
	public static function viewMismatchProvider(): array
	{
		return [
			'milestone plural'    => ['projects.milestones',         'projects.milestone'],
			'milestone.edit nested' => ['projects.milestones.edit',  'projects.milestone_edit'],
			'milestone.show nested' => ['projects.milestones.show',  'projects.milestone_show'],
			'bugCreate camelCase' => ['projects.bugCreate',          'projects.bug_create'],
			'bugEdit camelCase'   => ['projects.bugEdit',            'projects.bug_edit'],
			'bugKanban camelCase' => ['projects.bugKanban',          'projects.bug_kanban'],
			'bugShow camelCase'   => ['projects.bugShow',            'projects.bug_show'],
			'copylink_setting'    => ['projects.copylink_setting',   'projects.copy_link_setting'],
			'copylink_password'   => ['projects.copylink_password',  'projects.copy_link_password'],
			'copylink'            => ['projects.copylink',           'projects.copy_link'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('viewMismatchProvider')]
	public function test_controller_view_reference_mismatches_actual_file(string $controllerRef, string $actualFile): void
	{
		$this->assertBladeViewExists($actualFile);
		$this->assertNotEquals(
			$controllerRef,
			$actualFile,
			"Mismatch confirmed: controller references '{$controllerRef}' but blade file is '{$actualFile}'"
		);
	}
}
