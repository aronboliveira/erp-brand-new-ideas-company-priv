<?php

namespace Tests\Unit;

use App\Config\Constants\DatabaseConstants;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\NotificationTemplateLangs;
use App\Models\NotificationTemplates;
use App\Models\User;
use Spatie\Permission\Models\Permission;

class NotificationTemplatesControllerTest extends TestCase
{
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** index should redirect guests to login
	 **/
	public function index_redirects_guests_to_login()
	{
		$response = $this->get(route('notification-templates.index', [1, 'en']));
		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** index should deny access to users without permission
	 **/
	public function index_denies_access_to_unauthorized_user()
	{
		$user = User::factory()->create();
		$response = $this->actingAs($user)
			->get(route('notification-templates.index', [1, 'en']));
		$response->assertStatus(302);
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** update should redirect back with error on validation failure
	 **/
	public function update_redirects_back_on_validation_failure()
	{
		Permission::create(['name' => 'edit notification template']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit notification template');

		// Insert a dummy template
		DB::table('notification_templates')->insert([
			'id'         => 1,
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->from(route('notification-templates.index', [1, 'en']))
			->put(route('notification-templates.update', 1), [
				// missing 'content'
				'lang' => 'pt',
			]);

		$response->assertRedirect(route('notification-templates.index', [1, 'en']));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** update should create a new translation and redirect on success
	 **/
	public function update_creates_translation_and_redirects_on_success()
	{
		Permission::create(['name' => 'edit notification template']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit notification template');

		// Insert a dummy template
		DB::table('notification_templates')->insert([
			'id'         => 2,
			'created_by' => $user?->creatorId(),
		]);

		$payload = [
			'lang'    => 'es',
			'content' => 'Contenido en español',
		];

		$response = $this->actingAs($user)
			->put(route('notification-templates.update', 2), $payload);

		$response->assertRedirect(route('notification-templates.index', [2, 'es']));
		$this->assertDatabaseHas(DatabaseConstants::TABLE_NOTIFICATION_TEMPLATES, [
			'parent_id'  => 2,
			'lang'       => 'es',
			'content'    => 'Contenido en español',
			'created_by' => $user?->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** update should modify an existing translation
	 **/
	public function update_modifies_existing_translation()
	{
		Permission::create(['name' => 'edit notification template']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit notification template');

		// Insert a dummy template
		DB::table('notification_templates')->insert([
			'id'         => 3,
			'created_by' => $user?->creatorId(),
		]);

		// Pre-insert a translation
		NotificationTemplateLangs::unguard();
		NotificationTemplateLangs::create([
			'parent_id'  => 3,
			'lang'       => 'de',
			'variables'  => '[]',
			'content'    => 'Alter Inhalt',
			'created_by' => $user?->creatorId(),
		]);
		NotificationTemplateLangs::reguard();

		$payload = [
			'lang'    => 'de',
			'content' => 'Neuer Inhalt',
		];

		$response = $this->actingAs($user)
			->put(route('notification-templates.update', 3), $payload);

		$response->assertRedirect(route('notification-templates.index', [3, 'de']));
		$this->assertDatabaseHas('notification_template_langs', [
			'parent_id' => 3,
			'lang'      => 'de',
			'content'   => 'Neuer Inhalt',
		]);
	}
}
