<?php

namespace Tests\Feature;

use App\Models\{EmailTemplate, EmailTemplateLang, Language, User, UserEmailTemplate};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class EmailTemplateControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $superAdmin;
	private User $company;
	private User $employee;

	protected function setUp(): void
	{
		parent::setUp();

		// allow all permission checks by default
		Gate::before(fn () => true);

		// create users of various types
		$this->superAdmin = User::factory()->create(['type' => 'super admin']);
		$this->company   = User::factory()->create(['type' => 'company']);
		$this->employee  = User::factory()->create(['type' => 'Employee']);
	}

	/**
	 ** @test
	 **
	 ** index_lists_templates_for_super_admin_and_company_only
	 **
	 ** Should show list view for super admin and company, deny others.
	 **/
	public function index_lists_templates_for_super_admin_and_company_only()
	{
		EmailTemplate::factory()->count(3)->create();

		// super admin
		$resp = $this->actingAs($this->superAdmin)
			->get(route('email_template.index'));
		$resp->assertOk()
			->assertViewIs('settings.company')
			->assertViewHas('templates', fn ($t) => $t->count() === 3);

		// company
		$resp = $this->actingAs($this->company)
			->get(route('email_template.index'));
		$resp->assertOk();

		// employee denied
		$resp = $this->actingAs($this->employee)
			->get(route('email_template.index'));
		$resp->assertRedirect()
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** create_returns_create_view
	 **/
	public function create_returns_create_view()
	{
		$resp = $this->actingAs($this->company)
			->get(route('email_template.create'));
		$resp->assertOk()
			->assertViewIs('email_templates.create');
	}

	/**
	 ** @test
	 **
	 ** store_validates_and_creates_template
	 **/
	public function store_validates_and_creates_template()
	{
		// missing name
		$this->actingAs($this->company)
			->post(route('email_template.store'), [])
			->assertSessionHasErrors('name');

		// valid
		$this->actingAs($this->company)
			->post(route('email_template.store'), ['name' => 'Welcome'])
			->assertRedirect(route('email_template.index'))
			->assertSessionHas('success');
		$this->assertDatabaseHas('email_templates', ['name' => 'Welcome']);
	}

	/**
	 ** @test
	 **
	 ** show_and_edit_redirect_with_permission_denied
	 **/
	public function show_and_edit_redirect_with_permission_denied()
	{
		$tpl = EmailTemplate::factory()->create();
		$this->actingAs($this->company)
			->get(route('email_template.show', $tpl->id))
			->assertRedirect()
			->assertSessionHas('error');
		$this->actingAs($this->company)
			->get(route('email_template.edit', $tpl->id))
			->assertRedirect()
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** update_validates_and_saves_email_and_language
	 **/
	public function update_validates_and_saves_email_and_language()
	{
		$tpl = EmailTemplate::factory()->create(['from' => 'old@example.com']);
		Language::factory()->create(['code' => 'fr']);

		// missing fields
		$this->actingAs($this->superAdmin)
			->put(route('email_template.update', $tpl->id), [])
			->assertSessionHasErrors(['from', 'subject', 'content', 'lang']);

		// valid
		$payload = [
			'from'    => 'new@example.com',
			'subject' => 'Salut',
			'content' => '<p>Bonjour</p>',
			'lang'    => 'fr',
		];
		$this->actingAs($this->superAdmin)
			->put(route('email_template.update', $tpl->id), $payload)
			->assertRedirect(route('manage.email.language', [$tpl->id, 'fr']))
			->assertSessionHas('success');

		$this->assertDatabaseHas('email_templates', [
			'id'   => $tpl->id,
			'from' => 'new@example.com',
		]);
		$this->assertDatabaseHas('email_template_langs', [
			'parent_id' => $tpl->id,
			'lang'      => 'fr',
			'subject'   => 'Salut',
		]);
	}

	/**
	 ** @test
	 **
	 ** destroy_redirects_with_permission_denied
	 **/
	public function destroy_redirects_with_permission_denied()
	{
		$tpl = EmailTemplate::factory()->create();
		$this->actingAs($this->company)
			->delete(route('email_template.destroy', $tpl->id))
			->assertRedirect()
			->assertSessionHas('error');
	}

	/**
	 ** @test
	 **
	 ** manage_email_language_requires_super_admin_and_displays_view
	 **/
	public function manage_email_language_requires_super_admin_and_displays_view()
	{
		$tpl = EmailTemplate::factory()->create();
		Language::factory()->create(['code' => 'es']);
		EmailTemplateLang::factory()->create([
			'parent_id' => $tpl->id,
			'lang'      => 'es',
		]);

		// company denied
		$this->actingAs($this->company)
			->get(route('manage.email.language', [$tpl->id, 'es']))
			->assertRedirect()
			->assertSessionHas('error');

		// super admin sees view
		$resp = $this->actingAs($this->superAdmin)
			->get(route('manage.email.language', [$tpl->id, 'es']));
		$resp->assertOk()
			->assertViewIs('email_templates.show')
			->assertViewHasAll([
				'template', 'languages', 'langTemplate', 'allTemplates', 'langName'
			]);
	}

	/**
	 ** @test
	 **
	 ** store_email_language_validates_and_persists
	 **/
	public function store_email_language_validates_and_persists()
	{
		$tpl = EmailTemplate::factory()->create();

		// missing
		$this->actingAs($this->superAdmin)
			->post(route('email_template.store_language', $tpl->id), [])
			->assertSessionHasErrors(['subject', 'content', 'lang']);

		// valid
		Language::factory()->create(['code' => 'de']);
		$data = [
			'lang'    => 'de',
			'subject' => 'Hallo',
			'content' => '<p>Grüße</p>',
		];
		$this->actingAs($this->superAdmin)
			->post(route('email_template.store_language', $tpl->id), $data)
			->assertRedirect(route('manage.email.language', [$tpl->id, 'de']))
			->assertSessionHas('success');
		$this->assertDatabaseHas('email_template_langs', [
			'parent_id' => $tpl->id,
			'lang'      => 'de',
		]);
	}

	/**
	 ** @test
	 **
	 ** updateStatus_requires_company_or_super_admin_and_saves_settings
	 **/
	public function updateStatus_requires_company_or_super_admin_and_saves_settings()
	{
		$tpl1 = EmailTemplate::factory()->create();
		$tpl2 = EmailTemplate::factory()->create();

		// employee denied
		$this->actingAs($this->employee)
			->post(route('email_template.update_status'), [])
			->assertRedirect()
			->assertSessionHas('error');

		// company can
		$statuses = [$tpl1->id => 1, $tpl2->id => 0];
		$this->actingAs($this->company)
			->post(route('email_template.update_status'), $statuses)
			->assertRedirect()
			->assertSessionHas('success');
		$this->assertDatabaseHas('user_email_templates', [
			'user_id'     => $this->company->id,
			'template_id' => $tpl1->id,
			'is_active'   => 1,
		]);
		$this->assertDatabaseHas('user_email_templates', [
			'user_id'     => $this->company->id,
			'template_id' => $tpl2->id,
			'is_active'   => 0,
		]);

		// super admin can also
		$statuses = [$tpl1->id => 0, $tpl2->id => 1];
		$this->actingAs($this->superAdmin)
			->post(route('email_template.update_status'), $statuses)
			->assertRedirect()
			->assertSessionHas('success');
	}
}
