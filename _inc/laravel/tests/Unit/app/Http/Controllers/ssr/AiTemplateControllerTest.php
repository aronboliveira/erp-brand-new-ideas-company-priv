<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Template;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AiTemplateController;

class AiTemplateControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;
	private string $module = 'offers';

	protected function setUp(): void
	{
		parent::setUp();
		// Create a test user
		$this->user = User::factory()->create();
	}

	/**
	 ** @test
	 **
	 ** The create action should redirect guests, redirect authenticated users
	 ** without the “manage ai template” permission, and with permission show
	 ** the generateAi view listing all templates for the given module.
	 **/
	public function create_requires_auth_and_manage_permission_and_displays_templates()
	{
		// Guest → redirect
		$resp = $this->get(action([AiTemplateController::class, 'create'], ['moduleName' => $this->module]));
		$resp->assertRedirect();

		// Authenticated without permission → redirect
		$resp = $this->actingAs($this->user)
			->get(action([AiTemplateController::class, 'create'], ['moduleName' => $this->module]));
		$resp->assertRedirect('/');

		// Grant permission and seed a template
		$this->user->givePermissionTo('manage ai template');
		$template = Template::create([
			'module'    => $this->module,
			'fieldJson' => json_encode(['field' => []]),
			'prompt'    => 'Hello ##name##',
			'isTone'    => false,
		]);

		// Now should see the generateAi view with our template
		$resp = $this->actingAs($this->user)
			->get(action([AiTemplateController::class, 'create'], ['moduleName' => $this->module]));

		$resp->assertOk()
			->assertViewIs('template.generateAi')
			->assertViewHas('templates', fn ($ts) => $ts->contains('id', $template->id));
	}

	/**
	 ** @test
	 **
	 ** The getKeywords action should require “manage ai template” permission,
	 ** then return JSON with `success`, `tone`, and an HTML form snippet
	 ** matching the template’s field definitions.
	 **/
	public function get_keywords_requires_auth_and_permission_and_returns_json()
	{
		$this->user->givePermissionTo('manage ai template');

		$fields = [
			['label' => 'Name', 'fieldType' => 'textBox', 'fieldName' => 'name', 'placeholder' => 'Enter name'],
			['label' => 'Desc', 'fieldType' => 'textarea', 'fieldName' => 'desc', 'placeholder' => 'Enter desc'],
		];
		$template = Template::create([
			'module'    => $this->module,
			'fieldJson' => json_encode(['field' => $fields]),
			'prompt'    => '',
			'isTone'    => true,
		]);

		// AJAX JSON request
		$resp = $this->actingAs($this->user)
			->getJson(action([AiTemplateController::class, 'getKeywords'], ['id' => $template->id]));

		$resp->assertOk()
			->assertJsonStructure(['success', 'tone', 'template'])
			->assertJson(['success' => true, 'tone' => true]);

		// HTML should include both field labels
		$html = $resp->json('template');
		$this->assertStringContainsString('Name', $html);
		$this->assertStringContainsString('Desc', $html);
	}

	/**
	 ** @test
	 **
	 ** The aiGenerate action should return a 400 for non-AJAX requests.
	 **/
	public function ai_generate_returns_400_for_non_ajax()
	{
		$resp = $this->actingAs($this->user)
			->post(action([AiTemplateController::class, 'aiGenerate']), []);
		$resp->assertStatus(400)
			->assertJson(['error' => 'Invalid request']);
	}

	/**
	 ** @test
	 **
	 ** The aiGenerate action should return an error JSON when no OpenAI key is configured.
	 **/
	public function ai_generate_returns_error_when_no_api_key()
	{
		$this->user->givePermissionTo('manage ai template');

		// AJAX JSON request without setting API key
		$resp = $this->actingAs($this->user)
			->postJson(action([AiTemplateController::class, 'aiGenerate']), [
				'_token'        => csrf_token(),
				'templateName'  => 1,
				'language'      => 'en',
				'tone'          => 'formal',
				'aiCreativity'  => '0.5',
				'numOfResult'   => '1',
				'resultLength'  => '50',
			]);

		$resp->assertStatus(500)
			->assertJson([
				'status'  => 'error',
				'message' => 'Please set proper configuration for Api Key',
			]);
	}

	/**
	 ** @test
	 **
	 ** The grammar action should redirect guests, and for authenticated users
	 ** display the grammarAi view with the first template for the module.
	 **/
	public function grammar_displays_view_for_module()
	{
		// Guest → redirect
		$resp = $this->get(action([AiTemplateController::class, 'grammar'], ['moduleName' => $this->module]));
		$resp->assertRedirect();

		// Authenticated → create a template and see the grammar view
		$this->actingAs($this->user);
		$template = Template::create([
			'module'    => $this->module,
			'fieldJson' => '{}',
			'prompt'    => '',
			'isTone'    => false,
		]);

		$resp = $this->actingAs($this->user)
			->get(action([AiTemplateController::class, 'grammar'], ['moduleName' => $this->module]));

		$resp->assertOk()
			->assertViewIs('template.grammarAi')
			->assertViewHas('template', fn ($t) => $t->id === $template->id);
	}

	/**
	 ** @test
	 **
	 ** The grammarProcess action should return 400 for non-AJAX requests.
	 **/
	public function grammar_process_returns_400_for_non_ajax()
	{
		$resp = $this->actingAs($this->user)
			->post(action([AiTemplateController::class, 'grammarProcess']), []);
		$resp->assertStatus(400)
			->assertJson(['error' => 'Invalid request']);
	}

	/**
	 ** @test
	 **
	 ** The grammarProcess action should return an error JSON when no OpenAI key is configured.
	 **/
	public function grammar_process_returns_error_when_no_api_key()
	{
		// AJAX JSON request without API key
		$resp = $this->actingAs($this->user)
			->postJson(action([AiTemplateController::class, 'grammarProcess']), [
				'_token'      => csrf_token(),
				'description' => 'Test text',
			]);

		$resp->assertStatus(500)
			->assertJson([
				'status'  => 'error',
				'message' => 'Please set proper configuration for Api Key',
			]);
	}
}
