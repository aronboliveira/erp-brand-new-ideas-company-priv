<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{
	Bug,
	BugComment,
	BugFile,
	Milestone,
	Project,
	ProjectUser,
	TimeTracker,
	User,
};
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Crypt, File, Storage};
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;

class ProjectControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $client;
	private User $viewer;
	private User $editor;
	private User $creator;
	private User $manager;
	private User $manageUser;
	private User $createUser;
	private User $editUser;
	private User $deleteUser;
	private User $deleterUser;
	private User $viewUser;
	private Project $project;
	private Project $existingProject;
	private User $clientUser;
	private User $otherUser;
	private User $editorUser;
	private User $viewGanttUser;
	private User $manageBugUser;
	private User $createBugUser;
	private User $editBugUser;
	private User $deleteBugUser;
	private User $moveBugUser;
	private User $viewBugUser;
	private Bug $bug;

	protected function setUp(): void
	{
		parent::setUp();

		// Permissions
		Permission::create(['name' => 'create milestone']);
		Permission::create(['name' => 'edit milestone']);
		Permission::create(['name' => 'delete milestone']);
		Permission::create(['name' => 'view milestone']);
		Permission::create(['name' => 'manage project']);
		Permission::create(['name' => 'create project']);
		Permission::create(['name' => 'edit project']);
		Permission::create(['name' => 'delete project']);

		// Users

		$this->createUser = User::factory()->create();
		$this->createUser->givePermissionTo('create project');

		$this->manageUser = User::factory()->create();
		$this->manageUser->givePermissionTo('manage project');

		$this->createUser = User::factory()->create();
		$this->createUser->givePermissionTo('create milestone');

		$this->editUser = User::factory()->create();
		$this->editUser->givePermissionTo('edit milestone');

		$this->deleteUser = User::factory()->create();
		$this->deleteUser->givePermissionTo('delete milestone');

		$this->viewUser = User::factory()->create();
		$this->viewUser->givePermissionTo('view milestone');

		$this->otherUser = User::factory()->create();

		// A project owned by createUser
		$this->project = Project::factory()->create([
			'created_by' => $this->createUser->creatorId(),
		]);

		$this->clientUser = User::factory()->create([
			'type'       => 'client',
			'created_by' => $this->createUser->creatorId(),
		]);

		$this->deleterUser = User::factory()->create();
		$this->deleterUser->givePermissionTo('delete project');

		$this->editorUser = User::factory()->create();
		$this->editorUser->givePermissionTo('edit project');

		$this->viewGanttUser = User::factory()->create();
		$this->viewGanttUser->givePermissionTo('view grant chart');
		$this->viewGanttUser->givePermissionTo('view project task');

		// Attach users so guard passes
		foreach ([
			$this->createUser,
			$this->editUser,
			$this->deleteUser,
			$this->viewUser
		] as $user) {
			$this->project->users()->attach($user?->id);
		}

		foreach ([
			'manage bug report',
			'create bug report',
			'edit bug report',
			'delete bug report',
			'move bug report',
			'view bug report',
		] as $perm) {
			Permission::create(['name' => $perm]);
		}

		// Create users and assign permissions
		$this->manageBugUser = User::factory()->create();
		$this->manageBugUser->givePermissionTo('manage bug report');

		$this->createBugUser = User::factory()->create();
		$this->createBugUser->givePermissionTo('create bug report');

		$this->editBugUser = User::factory()->create();
		$this->editBugUser->givePermissionTo('edit bug report');

		$this->deleteBugUser = User::factory()->create();
		$this->deleteBugUser->givePermissionTo('delete bug report');

		$this->moveBugUser = User::factory()->create();
		$this->moveBugUser->givePermissionTo('move bug report');

		$this->viewBugUser = User::factory()->create();
		$this->viewBugUser->givePermissionTo('view bug report');

		$this->otherUser = User::factory()->create();

		// Create a project owned by manageBugUser
		$this->project = Project::factory()->create([
			'created_by' => $this->manageBugUser->creatorId(),
		]);

		// Attach all perm’d users so guard passes
		foreach ([
			$this->manageBugUser,
			$this->createBugUser,
			$this->editBugUser,
			$this->deleteBugUser,
			$this->moveBugUser,
			$this->viewBugUser,
		] as $user) {
			$this->project->users()->attach($user?->id);
		}

		$this->bug = Bug::factory()->create([
			'project_id' => $this->project->id,
			'created_by' => $this->createBugUser->creatorId(),
		]);

		// Attach createBugUser so guard passes
		$this->project->users()->attach($this->createBugUser->id);
		$this->project->users()->attach($this->deleteBugUser->id);

		$this->manager = User::factory()->create();
		$this->manager->givePermissionTo('manage project');

		TimeTracker::factory()->count(3)->create([
			'project_id' => $this->project->id,
		]);

		$this->creator = User::factory()->create();
		$this->creator->givePermissionTo('create project');

		$this->viewer = User::factory()->create();
		$this->viewer->givePermissionTo('view project');

		$this->editor = User::factory()->create();
		$this->editor->givePermissionTo('edit project');

		// Existing project for show/edit/update/destroy
		$this->existingProject = Project::factory()->create([
			'created_by' => $this->manager->creatorId(),
		]);

		// Attach manager to project so guard passes for show/edit/destroy
		$this->existingProject->users()->attach($this->manager->id);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when accessing the project index.
	 **/
	public function guests_are_redirected_from_index()
	{
		$response = $this->get(route('projects.index'));

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'manage project' permission get a 302 on index.
	 **/
	public function unauthorized_users_cannot_access_index()
	{
		$user = User::factory()->create(); // no permission
		$response = $this->actingAs($user)->get(route('projects.index'));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'manage project' permission can view the index.
	 **/
	public function authorized_users_can_view_index()
	{
		$response = $this->actingAs($this->manageUser)
			->get(route('projects.index'));

		$response->assertStatus(200)
			->assertViewIs('projects.index');
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when accessing the create form.
	 **/
	public function guests_are_redirected_from_create()
	{
		$response = $this->get(route('projects.create'));

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'create project' permission get a 302 on create.
	 **/
	public function unauthorized_users_cannot_access_create()
	{
		$response = $this->actingAs($this->manageUser) // has manage, not create
			->get(route('projects.create'));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'create project' permission can view the create form.
	 **/
	public function authorized_users_can_view_create()
	{
		$response = $this->actingAs($this->createUser)
			->get(route('projects.create'));

		$response->assertStatus(200)
			->assertViewIs('projects.create')
			->assertViewHasAll(['clients', 'users']);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from store endpoint.
	 **/
	public function guests_are_redirected_from_store()
	{
		$response = $this->post(route('projects.store'), []);
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'create project' permission get 302 on store.
	 **/
	public function unauthorized_users_cannot_store_project()
	{
		$user = User::factory()->create(); // no permission
		$payload = [];
		$response = $this->actingAs($user)->post(route('projects.store'), $payload);
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Validation errors when required fields are missing.
	 **/
	public function validation_errors_for_missing_fields()
	{
		$response = $this->actingAs($this->createUser)
			->post(route('projects.store'), []);
		$response->assertSessionHasErrors([
			'project_name',
			'start_date',
			'end_date',
			'project_image',
			'client',
			'status'
		]);
	}

	/**
	 ** @test
	 **
	 ** Authorized user can store a project with valid data and file upload.
	 **/
	public function authorized_user_can_store_project()
	{
		Storage::fake(); // fakes the default disk

		$start = Carbon::now()->toDateString();
		$end  = Carbon::now()->addDay()->toDateString();
		$file = UploadedFile::fake()->image('project.jpg')->size(100);

		$payload = [
			'project_name'   => 'Test Project',
			'start_date'     => $start,
			'end_date'       => $end,
			'project_image'  => $file,
			'client'         => $this->clientUser->id,
			'budget'         => 1000,
			'description'    => 'A test project',
			'status'         => 'planning',
			'estimated_hrs'  => 50,
			'tag'            => 'testing',
			'user'           => [$this->createUser->id], // assigned users
		];

		$response = $this->actingAs($this->createUser)
			->post(route('projects.store'), $payload);

		// Should redirect to index with success
		$response->assertRedirect(route('projects.index'))
			->assertSessionHas('success');

		// Project was created
		$project = Project::first();
		$this->assertNotNull($project);
		$this->assertEquals('Test Project', $project->project_name);
		$this->assertEquals($this->clientUser->id, $project->client_id);

		// File was stored
		Storage::assertExists($project->project_image);

		// Assigned user created
		$this->assertDatabaseHas('project_users', [
			'project_id' => $project->id,
			'user_id'    => $this->createUser->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from show endpoint.
	 **/
	public function guests_are_redirected_from_show()
	{
		$response = $this->get(route('projects.show', $this->project));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'view project' permission get 302 on show.
	 **/
	public function unauthorized_users_cannot_view_project()
	{
		$user = User::factory()->create();
		$response = $this->actingAs($user)
			->get(route('projects.show', $this->project));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'view project' permission can view the project.
	 **/
	public function authorized_users_can_view_project()
	{
		$response = $this->actingAs($this->viewUser)
			->get(route('projects.show', $this->project));

		$response->assertStatus(200)
			->assertViewIs('projects.view')
			->assertViewHasAll(['project', 'project_data', 'last_task']);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from edit endpoint.
	 **/
	public function guests_are_redirected_from_edit()
	{
		$response = $this->get(route('projects.edit', $this->project));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'edit project' permission get 302 on edit.
	 **/
	public function unauthorized_users_cannot_access_edit()
	{
		$response = $this->actingAs($this->viewUser) // has view, not edit
			->get(route('projects.edit', $this->project));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'edit project' permission can view the edit form.
	 **/
	public function authorized_users_can_view_edit_form()
	{
		$response = $this->actingAs($this->editUser)
			->get(route('projects.edit', $this->project));

		$response->assertStatus(200)
			->assertViewIs('projects.edit')
			->assertViewHasAll(['project', 'clients']);
	}

	/**
	 ** @test
	 **
	 ** Validation errors when updating with missing required fields.
	 **/
	public function validation_errors_on_update()
	{
		$response = $this->actingAs($this->editUser)
			->put(route('projects.update', $this->project), []);
		$response->assertSessionHasErrors(['project_name', 'start_date', 'end_date', 'client', 'status']);
	}

	/**
	 ** @test
	 **
	 ** Authorized user can update project name and redirect.
	 **/
	public function authorized_users_can_update_project()
	{
		$newName  = 'Updated Project';
		$start    = Carbon::now()->toDateString();
		$end      = Carbon::now()->addDay()->toDateString();

		$response = $this->actingAs($this->editUser)
			->from(route('projects.edit', $this->project))
			->put(route('projects.update', $this->project), [
				'project_name'  => $newName,
				'start_date'    => $start,
				'end_date'      => $end,
				'client'        => $this->viewUser->id,
				'status'        => 'in_progress',
			]);

		$response->assertRedirect(route('projects.index'))
			->assertSessionHas('success');

		$this->assertDatabaseHas('projects', [
			'id'           => $this->project->id,
			'project_name' => $newName,
			'status'       => 'in_progress',
		]);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from destroy endpoint.
	 **/
	public function guests_are_redirected_from_destroy()
	{
		$response = $this->delete(route('projects.destroy', $this->project));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'delete project' permission get 302 on destroy.
	 **/
	public function unauthorized_users_cannot_delete_project()
	{
		$user = User::factory()->create();
		$response = $this->actingAs($user)
			->delete(route('projects.destroy', $this->project));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Authorized user can delete project and redirect back with success.
	 **/
	public function authorized_users_can_delete_project()
	{
		$response = $this->actingAs($this->deleteUser)
			->delete(route('projects.destroy', $this->project));

		$response->assertRedirect()
			->assertSessionHas('success', __('Project Successfully Deleted.'));

		$this->assertModelMissing($this->project);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from inviteMemberView.
	 **/
	public function guests_are_redirected_from_invite_member_view()
	{
		$url = route('projects.invite.view', ['projectId' => $this->project->id] ?? []);
		$response = $this->get($url);

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'manage project' permission cannot access inviteMemberView.
	 **/
	public function unauthorized_users_cannot_access_invite_member_view()
	{
		$response = $this->actingAs($this->otherUser)
			->get(route('projects.invite.view', $this->project->id));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'manage project' permission can view inviteMemberView.
	 **/
	public function authorized_users_can_view_invite_member_view()
	{
		$response = $this->actingAs($this->manageUser)
			->get(route('projects.invite.view', $this->project->id));

		$response->assertStatus(200)
			->assertViewIs('projects.invite')
			->assertViewHasAll(['projectId', 'users']);
	}

	/**
	 ** @test
	 **
	 ** Guests receive 401 JSON when calling inviteProjectUserMember.
	 **/
	public function guests_receive_401_on_invite_project_user_member()
	{
		$url = route('projects.invite');
		$response = $this->postJson($url, ['project_id' => $this->project->id, 'user_id' => $this->otherUser->id]);

		$response->assertStatus(401)
			->assertJson(['error' => 'Permission denied.']);
	}

	/**
	 ** @test
	 **
	 ** Users without 'edit project' permission receive 401 JSON on inviteProjectUserMember.
	 **/
	public function unauthorized_users_receive_401_on_invite_project_user_member()
	{
		$response = $this->actingAs($this->manageUser) // has manage, not edit
			->postJson(route('projects.invite'), ['project_id' => $this->project->id, 'user_id' => $this->otherUser->id]);

		$response->assertStatus(401)
			->assertJson(['error' => 'Permission denied.']);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can invite a project member.
	 **/
	public function authorized_users_can_invite_project_member()
	{
		$response = $this->actingAs($this->editorUser)
			->postJson(route('projects.invite'), [
				'project_id' => $this->project->id,
				'user_id'    => $this->otherUser->id,
			]);

		$response->assertStatus(200)
			->assertJson(['status' => 'success', 'code' => 200]);

		$this->assertDatabaseHas('project_users', [
			'project_id' => $this->project->id,
			'user_id'    => $this->otherUser->id,
		]);

		$this->assertDatabaseHas('activity_logs', [
			'project_id' => $this->project->id,
			'log_type'   => 'Invite User',
		]);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from destroyProjectUser.
	 **/
	public function guests_are_redirected_from_destroy_projectUser()
	{
		$url = route('projects.user.destroy', ['projectId' => $this->project->id, 'userId' => $this->otherUser->id]);
		$response = $this->delete($url);

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'delete project' permission get 302 on destroyProjectUser.
	 **/
	public function unauthorized_users_cannot_destroy_projectUser()
	{
		// Assign otherUser so there is something to delete
		ProjectUser::create([
			'project_id' => $this->project->id,
			'user_id'    => $this->otherUser->id,
		]);

		$url = route('projects.user.destroy', ['projectId' => $this->project->id, 'userId' => $this->otherUser->id]);
		$response = $this->actingAs($this->manageUser) // has manage, not delete
			->delete($url);

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can destroy a project user.
	 **/
	public function authorized_users_can_destroy_projectUser()
	{
		// Assign otherUser first
		ProjectUser::create([
			'project_id' => $this->project->id,
			'user_id'    => $this->otherUser->id,
		]);

		$url = route('projects.user.destroy', ['projectId' => $this->project->id, 'userId' => $this->otherUser->id]);
		$response = $this->actingAs($this->deleterUser)
			->delete($url);

		$response->assertRedirect()
			->assertSessionHas('success', __('User successfully deleted!'));

		$this->assertDatabaseMissing('project_users', [
			'project_id' => $this->project->id,
			'user_id'    => $this->otherUser->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Authorized AJAX loadUser returns HTML snippet.
	 **/
	public function authorized_ajax_load_user_returns_html()
	{
		// Add another member
		$extraUser = User::factory()->create();
		ProjectUser::create([
			'project_id' => $this->project->id,
			'user_id'    => $extraUser->id,
		]);

		$response = $this->actingAs($this->manageUser)
			->getJson(route('projects.loadUser'), [
				'project_id' => $this->project->id,
			]);

		$response->assertStatus(200)
			->assertJson(['success' => true])
			->assertJsonStructure(['html']);
	}
	/**
	 ** @test
	 **
	 ** Guests are redirected from the milestone page when not authenticated.
	 **/
	public function guests_are_redirected_from_milestone_page()
	{
		$response = $this->get(route('projects.milestone', $this->project->id));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'create milestone' permission receive a 302 when accessing the milestone page.
	 **/
	public function unauthorized_users_cannot_access_milestone_page()
	{
		$user = User::factory()->create(); // no permission
		$response = $this->actingAs($user)
			->get(route('projects.milestone', $this->project->id));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'create milestone' permission can view the milestone creation page.
	 **/
	public function authorized_users_can_view_milestone_page()
	{
		$response = $this->actingAs($this->createUser)
			->get(route('projects.milestone', $this->project->id));
		$response->assertStatus(200)
			->assertViewIs('projects.milestone')
			->assertViewHas('project');
	}

	/**
	 ** @test
	 **
	 ** Validation errors are returned when required fields are missing on store.
	 **/
	public function validation_errors_on_milestone_store()
	{
		$response = $this->actingAs($this->createUser)
			->post(route('projects.milestone.store', $this->project->id), []);
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Authorized users can successfully store a milestone.
	 **/
	public function authorized_users_can_store_milestone()
	{
		$payload = [
			'title'      => 'Milestone 1',
			'status'     => 'pending',
			'cost'       => 500,
			'start_date' => Carbon::now()->toDateString(),
			'due_date'   => Carbon::now()->addDay()->toDateString(),
			'description' => 'Desc',
		];

		$response = $this->actingAs($this->createUser)
			->post(route('projects.milestone.store', $this->project->id), $payload);

		$response->assertRedirect()
			->assertSessionHas('success', __('Milestone successfully created.'));

		$this->assertDatabaseHas('milestones', [
			'project_id' => $this->project->id,
			'title'      => 'Milestone 1',
		]);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from the milestone edit form when not authenticated.
	 **/
	public function guests_are_redirected_from_milestone_edit()
	{
		$milestone = Milestone::factory()->create(['project_id' => $this->project->id]);
		$response = $this->get(route('projects.milestone.edit', $milestone->id));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'edit milestone' permission get a 302 when accessing the edit form.
	 **/
	public function unauthorized_users_cannot_access_milestone_edit()
	{
		$milestone = Milestone::factory()->create(['project_id' => $this->project->id]);
		$user = User::factory()->create();
		$response = $this->actingAs($user)
			->get(route('projects.milestone.edit', $milestone->id));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'edit milestone' permission can view the milestone edit form.
	 **/
	public function authorized_users_can_view_milestone_edit_form()
	{
		$milestone = Milestone::factory()->create(['project_id' => $this->project->id]);
		$response = $this->actingAs($this->editUser)
			->get(route('projects.milestone.edit', $milestone->id));
		$response->assertStatus(200)
			->assertViewIs('projects.milestoneEdit')
			->assertViewHas('milestone');
	}

	/**
	 ** @test
	 **
	 ** Validation errors are returned when updating a milestone with missing data.
	 **/
	public function validation_errors_on_milestone_update()
	{
		$milestone = Milestone::factory()->create(['project_id' => $this->project->id]);
		$response = $this->actingAs($this->editUser)
			->put(route('projects.milestone.update', $milestone->id), []);
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** Authorized users can successfully update a milestone.
	 **/
	public function authorized_users_can_update_milestone()
	{
		$milestone = Milestone::factory()->create(['project_id' => $this->project->id]);
		$payload = [
			'title'      => 'Updated',
			'status'     => 'complete',
			'cost'       => 600,
			'start_date' => Carbon::now()->toDateString(),
			'due_date'   => Carbon::now()->addDays(2)->toDateString(),
		];

		$response = $this->actingAs($this->editUser)
			->put(route('projects.milestone.update', $milestone->id), $payload);

		$response->assertRedirect()
			->assertSessionHas('success', __('Milestone updated successfully.'));

		$this->assertDatabaseHas('milestones', [
			'id'     => $milestone->id,
			'title'  => 'Updated',
			'status' => 'complete',
		]);
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users cannot destroy a milestone.
	 **/
	public function unauthorized_users_cannot_destroy_milestone()
	{
		$milestone = Milestone::factory()->create(['project_id' => $this->project->id]);
		$user = User::factory()->create();
		$response = $this->actingAs($user)
			->delete(route('projects.milestone.destroy', $milestone->id));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can successfully destroy a milestone.
	 **/
	public function authorized_users_can_destroy_milestone()
	{
		$milestone = Milestone::factory()->create(['project_id' => $this->project->id]);
		$response = $this->actingAs($this->deleteUser)
			->delete(route('projects.milestone.destroy', $milestone->id));

		$response->assertRedirect()
			->assertSessionHas('success', __('Milestone successfully deleted.'));

		$this->assertModelMissing($milestone);
	}

	/**
	 ** @test
	 **
	 ** Users with 'view milestone' permission can view a milestone detail page.
	 **/
	public function authorized_users_can_view_milestone_show()
	{
		$milestone = Milestone::factory()->create(['project_id' => $this->project->id]);
		$response = $this->actingAs($this->viewUser)
			->get(route('projects.milestone.show', $milestone->id));

		$response->assertStatus(200)
			->assertViewIs('projects.milestoneShow')
			->assertViewHas('milestone');
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users cannot view a milestone detail page.
	 **/
	public function unauthorized_users_cannot_view_milestone_show()
	{
		$milestone = Milestone::factory()->create(['project_id' => $this->project->id]);
		$user = User::factory()->create();
		$response = $this->actingAs($user)
			->get(route('projects.milestone.show', $milestone->id));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from the AJAX filterProjectView endpoint.
	 **/
	public function guests_are_redirected_from_filter_project_view()
	{
		$response = $this->postJson(route('projects.filter'), [
			'view'   => 'grid',
			'sort'   => 'project_name-asc',
			'keyword' => '',
			'status' => [],
		]);

		$response->assertStatus(401);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can filter projects via AJAX and receive HTML.
	 **/
	public function authorized_users_can_filter_project_view()
	{
		// Acting as manageUser who has 'manage project'
		$response = $this->actingAs($this->manageUser)
			->postJson(route('projects.filter'), [
				'view'   => 'grid',
				'sort'   => 'project_name-asc',
				'keyword' => $this->project->project_name,
				'status' => [$this->project->status],
			]);

		$response->assertStatus(200)
			->assertJson(['success' => true])
			->assertJsonStructure(['html']);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from the gantt view endpoint.
	 **/
	public function guests_are_redirected_from_gantt_view()
	{
		$response = $this->get(route('projects.gantt', [
			'projectId' => $this->project->id,
			'duration'  => 'Week',
		]));

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users cannot access gantt chart page.
	 **/
	public function unauthorized_users_cannot_access_gantt()
	{
		$user = User::factory()->create();
		$response = $this->actingAs($user)
			->get(route('projects.gantt', [
				'projectId' => $this->project->id,
				'duration'  => 'Week',
			]));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'view grant chart' permission can view the gantt chart page.
	 **/
	public function authorized_users_can_view_gantt()
	{
		$response = $this->actingAs($this->viewGanttUser)
			->get(route('projects.gantt', [
				'projectId' => $this->project->id,
				'duration'  => 'Week',
			]));

		$response->assertStatus(200)
			->assertViewIs('projects.gantt')
			->assertViewHasAll(['project', 'tasks', 'duration']);
	}

	/**
	 ** @test
	 **
	 ** Users without 'view project task' permission cannot update gantt dates.
	 **/
	public function unauthorized_users_cannot_update_gantt_dates()
	{
		$user = User::factory()->create();
		$payload = [
			'task_id' => 'task_1',
			'start'   => Carbon::now()->toDateString(),
			'end'     => Carbon::now()->addDay()->toDateString(),
		];

		$response = $this->actingAs($user)
			->postJson(route('projects.gantt.post', $this->project->id), $payload);

		$response->assertStatus(400)
			->assertJson(['is_success' => false]);
	}

	/**
	 ** @test
	 **
	 ** Authorized users with 'view project task' can update gantt dates successfully.
	 **/
	public function authorized_users_can_update_gantt_dates()
	{
		// Create a dummy task so that ProjectTask::findOrFail will find one
		$task = $this->project->tasks()->create([
			'name'         => 'Task1',
			'start_date'   => Carbon::now(),
			'end_date'     => Carbon::now()->addDay(),
			'created_by'   => $this->viewGanttUser->creatorId(),
		]);

		$payload = [
			'task_id' => 'task_' . $task->id,
			'start'   => Carbon::now()->addDays(2)->toDateString(),
			'end'     => Carbon::now()->addDays(3)->toDateString(),
		];

		$response = $this->actingAs($this->viewGanttUser)
			->postJson(route('projects.gantt.post', $this->project->id), $payload);

		$response->assertStatus(200)
			->assertJson(['is_success' => true]);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from the bug listing page.
	 **/
	public function guests_are_redirected_from_bug_listing()
	{
		$response = $this->get(route('task.bug', $this->project->id));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'manage bug report' permission cannot view the bug listing.
	 **/
	public function unauthorized_users_cannot_view_bug_listing()
	{
		$response = $this->actingAs($this->otherUser)
			->get(route('task.bug', $this->project->id));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can view the bug listing for a project.
	 **/
	public function authorized_users_can_view_bug_listing()
	{
		$response = $this->actingAs($this->manageBugUser)
			->get(route('task.bug', $this->project->id));

		$response->assertStatus(200)
			->assertViewIs('projects.bug')
			->assertViewHasAll(['project', 'bugs']);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from the bug creation form.
	 **/
	public function guests_are_redirected_from_bug_create_form()
	{
		$response = $this->get(route('projects.bug.create', $this->project->id));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'create bug report' permission cannot access the bug creation form.
	 **/
	public function unauthorized_users_cannot_access_bug_create_form()
	{
		$response = $this->actingAs($this->manageBugUser) // has manage, not create
			->get(route('projects.bug.create', $this->project->id));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can view the bug creation form.
	 **/
	public function authorized_users_can_view_bug_create_form()
	{
		$response = $this->actingAs($this->createBugUser)
			->get(route('projects.bug.create', $this->project->id));

		$response->assertStatus(200)
			->assertViewIs('projects.bugCreate')
			->assertViewHasAll(['status', 'projectId', 'priority', 'users']);
	}

	/**
	 ** @test
	 **
	 ** Validation errors are returned when storing a bug with missing fields.
	 **/
	public function validation_errors_on_bug_store()
	{
		$response = $this->actingAs($this->createBugUser)
			->post(route('task.bug.store', $this->project->id), []);
		$response->assertSessionHasErrors(['title', 'priority', 'status', 'assign_to', 'start_date', 'due_date']);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can successfully store a bug report.
	 **/
	public function authorized_users_can_store_bug()
	{
		$payload = [
			'title'      => 'Bug Title',
			'priority'   => 'high',
			'status'     => 'open',
			'assign_to'  => $this->createBugUser->id,
			'start_date' => Carbon::now()->toDateString(),
			'due_date'   => Carbon::now()->addDay()->toDateString(),
			'description' => 'Bug details',
		];

		$response = $this->actingAs($this->createBugUser)
			->post(route('task.bug.store', $this->project->id), $payload);

		$response->assertRedirect(route('task.bug', $this->project->id))
			->assertSessionHas('success', __('Bug successfully created.'));

		$this->assertDatabaseHas('bugs', [
			'project_id' => $this->project->id,
			'title'      => 'Bug Title',
		]);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from the bug edit form.
	 **/
	public function guests_are_redirected_from_bug_edit_form()
	{
		$bug = Bug::factory()->create([
			'project_id' => $this->project->id,
			'assign_to'  => $this->manageBugUser->id,
			'created_by' => $this->manageBugUser->creatorId(),
		]);

		$response = $this->get(route('task.bug.edit', [$this->project->id, $bug->id]));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'edit bug report' permission cannot access the bug edit form.
	 **/
	public function unauthorized_users_cannot_access_bug_edit_form()
	{
		$bug = Bug::factory()->create([
			'project_id' => $this->project->id,
			'assign_to'  => $this->manageBugUser->id,
			'created_by' => $this->manageBugUser->creatorId(),
		]);

		$response = $this->actingAs($this->manageBugUser) // has manage, not edit
			->get(route('task.bug.edit', [$this->project->id, $bug->id]));
		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can view the bug edit form.
	 **/
	public function authorized_users_can_view_bug_edit_form()
	{
		$bug = Bug::factory()->create([
			'project_id' => $this->project->id,
			'assign_to'  => $this->createBugUser->id,
			'created_by' => $this->createBugUser->creatorId(),
		]);

		$response = $this->actingAs($this->editBugUser)
			->get(route('task.bug.edit', [$this->project->id, $bug->id]));

		$response->assertStatus(200)
			->assertViewIs('projects.bugEdit')
			->assertViewHasAll(['status', 'projectId', 'priority', 'users', 'bug']);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can successfully update a bug report.
	 **/
	public function authorized_users_can_update_bug()
	{
		$bug = Bug::factory()->create([
			'project_id' => $this->project->id,
			'assign_to'  => $this->createBugUser->id,
			'created_by' => $this->createBugUser->creatorId(),
		]);

		$payload = [
			'title'      => 'Updated Bug',
			'priority'   => 'low',
			'status'     => 'closed',
			'assign_to'  => $this->editBugUser->id,
			'start_date' => Carbon::now()->toDateString(),
			'due_date'   => Carbon::now()->addDays(2)->toDateString(),
			'description' => 'Updated desc',
		];

		$response = $this->actingAs($this->editBugUser)
			->put(route('task.bug.update', [$this->project->id, $bug->id]), $payload);

		$response->assertRedirect(route('task.bug', $this->project->id))
			->assertSessionHas('success', __('Bug successfully updated.'));

		$this->assertDatabaseHas('bugs', [
			'id'    => $bug->id,
			'title' => 'Updated Bug',
			'status' => 'closed',
		]);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can delete a bug report.
	 **/
	public function authorized_users_can_delete_bug()
	{
		$bug = Bug::factory()->create([
			'project_id' => $this->project->id,
			'assign_to'  => $this->createBugUser->id,
			'created_by' => $this->createBugUser->creatorId(),
		]);

		$response = $this->actingAs($this->deleteBugUser)
			->delete(route('task.bug.destroy', [$this->project->id, $bug->id]));

		$response->assertRedirect(route('task.bug', $this->project->id))
			->assertSessionHas('success', __('Bug successfully deleted.'));

		$this->assertModelMissing($bug);
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users cannot access the bug Kanban board.
	 **/
	public function unauthorized_users_cannot_access_bug_kanban()
	{
		$response = $this->actingAs($this->manageBugUser)
			->get(route('task.bug.kanban', $this->project->id));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can view the bug Kanban board.
	 **/
	public function authorized_users_can_view_bug_kanban()
	{
		$response = $this->actingAs($this->moveBugUser)
			->get(route('task.bug.kanban', $this->project->id));

		$response->assertStatus(200)
			->assertViewIs('projects.bugKanban')
			->assertViewHasAll(['project', 'bug_status']);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can reorder bugs on the Kanban board.
	 **/
	public function authorized_users_can_update_bug_kanban_order()
	{
		$bug = Bug::factory()->create([
			'project_id' => $this->project->id,
			'assign_to'  => $this->moveBugUser->id,
			'created_by' => $this->moveBugUser->creatorId(),
		]);

		$response = $this->actingAs($this->moveBugUser)
			->post(route('task.bug.kanban.order'), [
				'status_id' => $bug->status,
				'bug_id'    => $bug->id,
				'order'     => [$bug->id],
			]);

		$response->assertRedirect()
			->assertSessionHas('success', __('Order updated successfully.'));
	}

	/**
	 ** @test
	 **
	 ** Authorized users can view the bug detail page.
	 **/
	public function authorized_users_can_view_bug_show()
	{
		$bug = Bug::factory()->create([
			'project_id' => $this->project->id,
			'assign_to'  => $this->viewBugUser->id,
			'created_by' => $this->viewBugUser->creatorId(),
		]);

		$response = $this->actingAs($this->viewBugUser)
			->get(route('task.bug.show', [$this->project->id, $bug->id]));

		$response->assertStatus(200)
			->assertViewIs('projects.bugShow')
			->assertViewHas('bug');
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users cannot view the bug detail page.
	 **/
	public function unauthorized_users_cannot_view_bug_show()
	{
		$bug = Bug::factory()->create([
			'project_id' => $this->project->id,
			'assign_to'  => $this->manageBugUser->id,
			'created_by' => $this->manageBugUser->creatorId(),
		]);

		$response = $this->actingAs($this->otherUser)
			->get(route('task.bug.show', [$this->project->id, $bug->id]));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users receive 403 JSON when posting a bug comment.
	 **/
	public function unauthorized_users_cannot_post_bug_comment()
	{
		$response = $this->actingAs($this->otherUser)
			->postJson(route('bug.comment.store', [
				'projectId' => $this->project->id,
				'bugId'     => $this->bug->id,
			]), ['comment' => 'Hello']);

		$response->assertStatus(403)
			->assertJson(['is_success' => false, 'message' => __('Permission denied.')]);
	}

	/**
	 ** @test
	 **
	 ** Returns 400 JSON when posting a comment to a bug not belonging to project.
	 **/
	public function posting_bug_comment_to_wrong_project_returns_400()
	{
		$otherProject = Project::factory()->create();
		$response = $this->actingAs($this->createBugUser)
			->postJson(route('bug.comment.store', [
				'projectId' => $otherProject->id,
				'bugId'     => $this->bug->id,
			]), ['comment' => 'Test']);

		$response->assertStatus(400)
			->assertJson(['is_success' => false, 'message' => __('Invalid project or bug.')]);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can post a bug comment successfully.
	 **/
	public function authorized_users_can_post_bug_comment()
	{
		$response = $this->actingAs($this->createBugUser)
			->postJson(route('bug.comment.store', [
				'projectId' => $this->project->id,
				'bugId'     => $this->bug->id,
			]), ['comment' => 'New comment']);

		$response->assertStatus(200)
			->assertJson(['is_success' => true, 'message' => __('Bug comment successfully created.')])
			->assertJsonStructure(['data' => ['id', 'comment', 'deleteUrl']]);

		$this->assertDatabaseHas('bug_comments', [
			'bug_id'  => $this->bug->id,
			'comment' => 'New comment',
		]);
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users receive 403 JSON when deleting a bug comment.
	 **/
	public function unauthorized_users_cannot_delete_bug_comment()
	{
		$comment = BugComment::factory()->create([
			'bug_id'     => $this->bug->id,
			'created_by' => $this->createBugUser->creatorId(),
		]);

		$response = $this->actingAs($this->otherUser)
			->deleteJson(route('bug.comment.destroy', $comment->id));

		$response->assertStatus(403)
			->assertJson(['is_success' => false, 'message' => __('Permission denied.')]);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can delete a bug comment successfully.
	 **/
	public function authorized_users_can_delete_bug_comment()
	{
		$comment = BugComment::factory()->create([
			'bug_id'     => $this->bug->id,
			'created_by' => $this->createBugUser->creatorId(),
		]);

		$response = $this->actingAs($this->deleteBugUser)
			->deleteJson(route('bug.comment.destroy', $comment->id));

		$response->assertStatus(200)
			->assertJson(['is_success' => true]);

		$this->assertModelMissing($comment);
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users receive 403 JSON when uploading a bug comment file.
	 **/
	public function unauthorized_users_cannot_upload_bug_comment_file()
	{
		Storage::fake('bugs');
		$response = $this->actingAs($this->otherUser)
			->postJson(route('bug.comment.file.store', $this->bug->id), [
				'file' => UploadedFile::fake()->create('test.txt', 10),
			]);

		$response->assertStatus(403)
			->assertJson(['is_success' => false, 'message' => __('Permission denied.')]);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can upload a bug comment file successfully.
	 **/
	public function authorized_users_can_upload_bug_comment_file()
	{
		Storage::fake('bugs');
		$file = UploadedFile::fake()->create('test.pdf', 50);

		$response = $this->actingAs($this->createBugUser)
			->postJson(route('bug.comment.file.store', $this->bug->id), [
				'file' => $file,
			]);

		$response->assertStatus(200)
			->assertJsonStructure(['id', 'file', 'name', 'deleteUrl']);

		$this->assertDatabaseHas('bug_files', [
			'bug_id' => $this->bug->id,
			'name'   => 'test.pdf',
		]);
		$adapter = Storage::disk('bugs');
		assert($adapter instanceof FilesystemAdapter);
		$adapter->assertExists($response->json('file'));
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users receive 403 JSON when deleting a bug comment file.
	 **/
	public function unauthorized_users_cannot_delete_bug_comment_file()
	{
		$bf = BugFile::factory()->create([
			'bug_id'     => $this->bug->id,
			'file'       => 'bugs/test.doc',
			'created_by' => $this->createBugUser->creatorId(),
		]);

		$response = $this->actingAs($this->otherUser)
			->deleteJson(route('bug.comment.file.destroy', $bf->id));

		$response->assertStatus(403)
			->assertJson(['is_success' => false, 'message' => __('Permission denied.')]);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can delete a bug comment file and its stored file.
	 **/
	public function authorized_users_can_delete_bug_comment_file()
	{
		Storage::makeDirectory(storage_path('bugs'));
		File::put(storage_path('bugs/testfile.txt'), 'content');

		$bf = BugFile::factory()->create([
			'bug_id'     => $this->bug->id,
			'file'       => 'testfile.txt',
			'created_by' => $this->createBugUser->creatorId(),
		]);

		$response = $this->actingAs($this->deleteBugUser)
			->deleteJson(route('bug.comment.file.destroy', $bf->id));

		$response->assertStatus(200)
			->assertJson(['is_success' => true]);

		$this->assertModelMissing($bf);
		$this->assertFalse(File::exists(storage_path('bugs/testfile.txt')));
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when accessing the tracker index.
	 **/
	public function guests_are_redirected_from_tracker_index()
	{
		$url = action([\App\Http\Controllers\ProjectController::class, 'tracker'], ['projectId' => $this->project->id]);
		$response = $this->get($url);

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'manage project' permission receive a 302 when accessing tracker index.
	 **/
	public function unauthorized_users_cannot_access_tracker_index()
	{
		$url = action([\App\Http\Controllers\ProjectController::class, 'tracker'], ['projectId' => $this->project->id]);
		$response = $this->actingAs($this->otherUser)->get($url);

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'manage project' permission can view the tracker index with data.
	 **/
	public function authorized_users_can_view_tracker_index()
	{
		$url = action([\App\Http\Controllers\ProjectController::class, 'tracker'], ['projectId' => $this->project->id]);
		$response = $this->actingAs($this->manager)->get($url);

		$response->assertStatus(200)
			->assertViewIs('time_trackers.index')
			->assertViewHas('trackers', function ($trackers) {
				return $trackers->count() === 3;
			});
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from the copy project form.
	 **/
	public function guests_are_redirected_from_copy_project_form()
	{
		$url = action([\App\Http\Controllers\ProjectController::class, 'copyProject'], ['projectId' => $this->project->id]);
		$response = $this->get($url);

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users cannot access the copy project form.
	 **/
	public function unauthorized_users_cannot_access_copy_project_form()
	{
		$url = action([\App\Http\Controllers\ProjectController::class, 'copyProject'], ['projectId' => $this->project->id]);
		$response = $this->actingAs($this->otherUser)->get($url);

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'create project' permission can view the copy project form.
	 **/
	public function authorized_users_can_view_copy_project_form()
	{
		$url = action([\App\Http\Controllers\ProjectController::class, 'copyProject'], ['projectId' => $this->project->id]);
		$response = $this->actingAs($this->creator)->get($url);

		$response->assertStatus(200)
			->assertViewIs('projects.copy')
			->assertViewHas('project', $this->project);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can successfully duplicate a project.
	 **/
	public function authorized_users_can_duplicate_project()
	{
		$countBefore = Project::count();

		$url = action([\App\Http\Controllers\ProjectController::class, 'copyProjectStore'], ['projectId' => $this->project->id]);
		$response = $this->actingAs($this->creator)->post($url);

		$response->assertRedirect()
			->assertSessionHas('success', __('Project duplicated successfully.'));

		$this->assertEquals($countBefore + 1, Project::count());
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from the copy link settings form.
	 **/
	public function guests_are_redirected_from_copy_link_form()
	{
		$url = action([\App\Http\Controllers\ProjectController::class, 'copyLinkSettingCreate'], ['projectId' => $this->project->id]);
		$response = $this->get($url);

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'view project' permission cannot access the copy link form.
	 **/
	public function unauthorized_users_cannot_access_copy_link_form()
	{
		$url = action([\App\Http\Controllers\ProjectController::class, 'copyLinkSettingCreate'], ['projectId' => $this->project->id]);
		$response = $this->actingAs($this->otherUser)->get($url);

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'view project' permission can view the copy link settings form.
	 **/
	public function authorized_users_can_view_copy_link_form()
	{
		$url = action([\App\Http\Controllers\ProjectController::class, 'copyLinkSettingCreate'], ['projectId' => $this->project->id]);
		$response = $this->actingAs($this->viewer)->get($url);

		$response->assertStatus(200)
			->assertViewIs('projects.copylink_setting')
			->assertViewHasAll(['project', 'projectId', 'settings']);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can save copy link settings and update the project.
	 **/
	public function authorized_users_can_save_copy_link_settings()
	{
		$payload = [
			'basic_details'       => 'on',
			'member'              => 'on',
			'password_protected'  => 'on',
			'password'            => 'secret123',
		];

		$url = action([\App\Http\Controllers\ProjectController::class, 'copyLinkSetting'], ['projectId' => $this->project->id]);
		$response = $this->actingAs($this->editor)->post($url, $payload);

		$response->assertRedirect()
			->assertSessionHas('success', __('Copy Link Setting saved.'));

		$this->project->refresh();
		$settings = json_decode($this->project->copylinksetting, true);
		$this->assertEquals('on', $settings['basic_details']);
		$this->assertEquals('on', $settings['member']);
		$this->assertEquals('on', $settings['password_protected']);
		$this->assertEquals(base64_encode('secret123'), $this->project->password);
	}

	/**
	 ** @test
	 **
	 ** Invalid encrypted project ID redirects back with an error.
	 **/
	public function invalid_encrypted_project_id_redirects_with_error()
	{
		$url = action([\App\Http\Controllers\ProjectController::class, 'projectLink'], ['encrypted' => 'invalid', 'lang' => '']);
		$response = $this->get($url);

		$response->assertRedirect()
			->assertSessionHas('error', __('Project not found.'));
	}

	/**
	 ** @test
	 **
	 ** Valid encrypted project link shows the public copy view.
	 **/
	public function valid_encrypted_project_link_shows_public_copy_view()
	{
		$encrypted = Crypt::encrypt($this->project->id);
		$url = action([\App\Http\Controllers\ProjectController::class, 'projectLink'], ['encrypted' => $encrypted, 'lang' => 'en']);
		$response = $this->get($url);

		$response->assertStatus(200)
			->assertViewIs('projects.copylink')
			->assertViewHas('project', $this->project)
			->assertViewHas('lang', 'en');
	}

	/**
	 ** @test
	 **
	 ** Validation errors are returned when storing without required data.
	 **/
	public function validation_errors_on_store()
	{
		Storage::fake('local');

		$response = $this->actingAs($this->manager)
			->post(route('projects.store'), []);

		$response->assertSessionHasErrors([
			'project_name', 'start_date', 'end_date', 'project_image', 'client', 'status'
		]);
	}

	/**
	 ** @test
	 **
	 ** Users with 'create project' permission can successfully store a project.
	 **/
	public function authorized_users_can_store_project()
	{
		Storage::fake('local');

		$file = UploadedFile::fake()->image('project.png');
		$payload = [
			'project_name'  => 'Test Project',
			'start_date'    => Carbon::today()->toDateString(),
			'end_date'      => Carbon::today()->addDay()->toDateString(),
			'project_image' => $file,
			'client'        => $this->client->id,
			'budget'        => 1234,
			'description'   => 'Desc',
			'status'        => 'ongoing',
			'estimated_hrs' => 5,
			'tag'           => 'tag1',
		];

		$response = $this->actingAs($this->manager)
			->post(route('projects.store'), $payload);

		$response->assertRedirect(route('projects.index'))
			->assertSessionHas('success');

		$project = Project::where('project_name', 'Test Project')->first();
		$this->assertNotNull($project);
		$adapter = Storage::disk('local');
		assert($adapter instanceof FilesystemAdapter);
		$adapter->assertExists($project->project_image);
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users cannot view a project they are not assigned to.
	 **/
	public function unauthorized_users_cannot_view_show()
	{
		$response = $this->actingAs($this->otherUser)
			->get(route('projects.show', $this->existingProject->id));

		$response->assertStatus(302)
			->assertSessionHas('error', __('Permission Denied.'));
	}

	/**
	 ** @test
	 **
	 ** Users with 'view project' permission can view the project detail.
	 **/
	public function authorized_users_can_view_show()
	{
		$this->manager->givePermissionTo('view project');

		$response = $this->actingAs($this->manager)
			->get(route('projects.show', $this->existingProject->id));

		$response->assertStatus(200)
			->assertViewIs('projects.view')
			->assertViewHasAll(['project', 'project_data', 'last_task']);
	}
	/**
	 ** @test
	 **
	 ** Users with 'edit project' permission can view the edit form.
	 **/
	public function authorized_users_can_view_edit()
	{
		$this->manager->givePermissionTo('edit project');

		$response = $this->actingAs($this->manager)
			->get(route('projects.edit', $this->existingProject->id));

		$response->assertStatus(200)
			->assertViewIs('projects.edit')
			->assertViewHas('project');
	}

	/**
	 ** @test
	 **
	 ** Users with 'delete project' permission can delete a project.
	 **/
	public function authorized_users_can_destroy_project()
	{
		$this->manager->givePermissionTo('delete project');

		$response = $this->actingAs($this->manager)
			->delete(route('projects.destroy', $this->existingProject->id));

		$response->assertRedirect()
			->assertSessionHas('success', __('Project Successfully Deleted.'));

		$this->assertModelMissing($this->existingProject);
	}
}
