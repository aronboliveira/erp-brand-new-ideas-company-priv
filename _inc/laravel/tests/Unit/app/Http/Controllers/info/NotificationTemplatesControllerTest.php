<?php

namespace Tests\Unit;

use App\Config\Constants\DatabaseConstants;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Models\NotificationTemplateLang;
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
		$response = $this->get(route('notification_templates.index', [1, 'en']));
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
			->get(route('notification_templates.index', [1, 'en']));
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
		Permission::firstOrCreate(['name' => 'edit notification template']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit notification template');

		// Insert a dummy template
		DB::table('notification_templates')->insert([
			'id'         => 1,
			'created_by' => $user?->creatorId(),
		]);

		$response = $this->actingAs($user)
			->from(route('notification_templates.index', [1, 'en']))
			->put(route('notification_templates.update', 1), [
				// missing 'content'
				'lang' => 'pt',
			]);

		$response->assertRedirect(route('notification_templates.index', [1, 'en']));
		$response->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** update should create a new translation and redirect on success
	 **/
	public function update_creates_translation_and_redirects_on_success()
	{
		Permission::firstOrCreate(['name' => 'edit notification template']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit notification template');

		// Insert a dummy template
		$templateId = (string) \Illuminate\Support\Str::uuid();
		DB::table('notification_templates')->insert([
			'id'         => $templateId,
			'name'       => 'Test Template',
			'created_by' => $user?->creatorId(),
		]);

		$payload = [
			'lang'    => 'es',
			'content' => 'Contenido en español',
		];

		$response = $this->actingAs($user)
			->put(route('notification_templates.update', $templateId), $payload);

		$response->assertRedirect(route('notification_templates.index', [$templateId, 'es']));
		$this->assertDatabaseHas('notification_template_langs', [
			'parent_id'  => $templateId,
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
		Permission::firstOrCreate(['name' => 'edit notification template']);
		$user = User::factory()->create();
		$user?->givePermissionTo('edit notification template');

		// Insert a dummy template
		$templateId = (string) \Illuminate\Support\Str::uuid();
		DB::table('notification_templates')->insert([
			'id'         => $templateId,
			'name'       => 'Test Template 2',
			'created_by' => $user?->creatorId(),
		]);

		// Pre-insert a translation
		NotificationTemplateLang::unguard();
		NotificationTemplateLang::create([
			'parent_id'  => $templateId,
			'lang'       => 'de',
			'variables'  => '[]',
			'content'    => 'Alter Inhalt',
			'created_by' => $user?->creatorId(),
		]);
		NotificationTemplateLang::reguard();

		$payload = [
			'lang'    => 'de',
			'content' => 'Neuer Inhalt',
		];

		$response = $this->actingAs($user)
			->put(route('notification_templates.update', $templateId), $payload);

		$response->assertRedirect(route('notification_templates.index', [$templateId, 'de']));
		$this->assertDatabaseHas('notification_template_langs', [
			'parent_id' => $templateId,
			'lang'      => 'de',
			'content'   => 'Neuer Inhalt',
		]);
	}
}
