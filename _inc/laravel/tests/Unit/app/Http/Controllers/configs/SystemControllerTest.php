<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{IpRestrict, User, WebhookSetting};
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Artisan, DB, File, Mail, Storage};
use Illuminate\Http\UploadedFile;

class SystemControllerTest extends TestCase
{
	use RefreshDatabase;

	private User $user;
	private User $systemUser;
	private User $companyUser;
	private User $printUser;
	private User $authorizedUser;
	private User $unauthorizedUser;

	protected function setUp(): void
	{
		parent::setUp();

		// Create permissions
		Permission::create(['name' => 'manage system settings']);
		Permission::create(['name' => 'manage company settings']);
		Permission::create(['name' => 'manage print settings']);
		Permission::create(['name' => 'create webhook']);

		// Create users
		$this->user = User::factory()->create();
		$this->user->givePermissionTo('manage system settings');

		$this->systemUser = User::factory()->create();
		$this->systemUser->givePermissionTo('manage system settings');

		$this->companyUser = User::factory()->create();
		$this->companyUser->givePermissionTo('manage company settings');

		$this->printUser = User::factory()->create();
		$this->printUser->givePermissionTo('manage print settings');


		$this->authorizedUser = User::factory()->create();
		$this->authorizedUser->givePermissionTo('create webhook');

		$this->unauthorizedUser = User::factory()->create();
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when accessing the system settings index.
	 **/
	public function guests_are_redirected_from_system_index()
	{
		$response = $this->get(route('settings.index'));

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'manage system settings' permission cannot access the settings index.
	 **/
	public function unauthorized_users_cannot_access_system_index()
	{
		$user = User::factory()->create(); // no permission
		$response = $this->actingAs($user)
			->get(route('settings.index'));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'manage system settings' permission can view the settings index.
	 **/
	public function authorized_users_can_view_system_index()
	{
		$response = $this->actingAs($this->systemUser)
			->get(route('settings.index'));

		$response->assertStatus(200)
			->assertViewIs('settings.index')
			->assertViewHasAll([
				'settings',
				'adminPaymentSetting',
				'fileSize',
			]);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when accessing the company settings page.
	 **/
	public function guests_are_redirected_from_company_index()
	{
		$response = $this->get(route('settings.company'));

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'manage company settings' permission cannot access the company settings page.
	 **/
	public function unauthorized_users_cannot_access_company_index()
	{
		$response = $this->actingAs($this->systemUser) // has system, not company perm
			->get(route('settings.company'));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'manage company settings' permission can view the company settings page.
	 **/
	public function authorized_users_can_view_company_index()
	{
		$response = $this->actingAs($this->companyUser)
			->get(route('settings.company'));

		$response->assertStatus(200)
			->assertViewIs('settings.company')
			->assertViewHasAll([
				'setting',
				'timezones',
				'companyPaymentSetting',
				'emailTemplates',
				'ips',
				'offerLetters',
				'currOfferLetter',
				'joiningLetters',
				'currJoiningLetter',
				'expCertificates',
				'currExpCert',
				'nocCertificates',
				'currNocCert',
			]);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected to login when accessing the print settings page.
	 **/
	public function guests_are_redirected_from_print_index()
	{
		$response = $this->get(route('settings.print'));

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Users without 'manage print settings' permission cannot access the print settings page.
	 **/
	public function unauthorized_users_cannot_access_print_index()
	{
		$response = $this->actingAs($this->systemUser) // has system, not print perm
			->get(route('settings.print'));

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Users with 'manage print settings' permission can view the print settings page.
	 **/
	public function authorized_users_can_view_print_index()
	{
		$response = $this->actingAs($this->printUser)
			->get(route('settings.print'));

		$response->assertStatus(200)
			->assertViewIs('settings.print')
			->assertViewHas('settings');
	}

	/**
	 ** @test
	 **
	 ** Users with 'manage print settings' permission can view the POS print settings page.
	 **/
	public function authorized_users_can_view_pos_print_index()
	{
		$response = $this->actingAs($this->printUser)
			->get(route('settings.pos'));

		$response->assertStatus(200)
			->assertViewIs('settings.pos')
			->assertViewHas('settings');
	}


	/**
	 ** @test
	 **
	 ** Authorized user can store brand settings without uploading any files.
	 **/
	public function authorized_user_can_store_brand_settings_without_files()
	{
		$response = $this->actingAs($this->systemUser)
			->post(route('settings.store'), [
				'SITE_RTL'            => 'on',
				'displayLandingPage'  => 'off',
				'gdprCookie'          => 'on',
				'enableSignup'        => 'off',
				'emailVerification'   => 'on',
				'custThemeBg'         => 'off',
				'custDarklayout'      => 'on',
			]);

		$response->assertRedirect()
			->assertSessionHas('success', __('Brand setting successfully updated.'));
	}

	/**
	 ** @test
	 **
	 ** Authorized user can upload logo files when storing brand settings.
	 **/
	public function authorized_user_can_store_brand_settings_with_files()
	{
		Storage::fake('uploads');
		$logo = UploadedFile::fake()->image('logo.png');

		$response = $this->actingAs($this->systemUser)
			->post(route('settings.store'), [
				'logoDark'  => $logo,
				'logoLight' => $logo,
				'favicon'   => $logo,
			]);

		$response->assertRedirect()
			->assertSessionHas('success', __('Brand setting successfully updated.'));
	}

	/**
	 ** @test
	 **
	 ** Authorized user can save general email settings.
	 **/
	public function authorized_user_can_save_email_settings()
	{
		$data = [
			'mailDriver'     => 'smtp',
			'mailHost'       => 'smtp.example.com',
			'mailPort'       => '587',
			'mailUsername'   => 'user',
			'mailPassword'   => 'secret',
			'mailEncryption' => 'tls',
			'mailFromAddress' => 'from@example.com',
			'mailFromName'   => 'Example',
		];

		$response = $this->actingAs($this->systemUser)
			->post(route('settings.email'), $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Setting successfully updated.'));
	}

	/**
	 ** @test
	 **
	 ** Non-company users cannot save company email settings.
	 **/
	public function non_company_user_cannot_save_company_email_settings()
	{
		$data = [
			'mailDriver'     => 'smtp',
			'mailHost'       => 'smtp.example.com',
			'mailPort'       => '587',
			'mailUsername'   => 'user',
			'mailPassword'   => 'secret',
			'mailEncryption' => 'tls',
			'mailFromAddress' => 'from@example.com',
			'mailFromName'   => 'Example',
		];

		$response = $this->actingAs($this->systemUser)
			->post(route('settings.company.email'), $data);

		$response->assertStatus(302);
	}

	/**
	 ** @test
	 **
	 ** Company users can save their email settings.
	 **/
	public function company_user_can_save_company_email_settings()
	{
		$data = [
			'mailDriver'     => 'smtp',
			'mailHost'       => 'smtp.example.com',
			'mailPort'       => '587',
			'mailUsername'   => 'user',
			'mailPassword'   => 'secret',
			'mailEncryption' => 'tls',
			'mailFromAddress' => 'from@example.com',
			'mailFromName'   => 'Example',
		];

		$response = $this->actingAs($this->companyUser)
			->post(route('settings.company.email'), $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Setting successfully updated.'));
	}

	/**
	 ** @test
	 **
	 ** Authorized user can save general payment settings.
	 **/
	public function authorized_user_can_save_payment_settings()
	{
		$data = [
			'currency'       => 'USD',
			'currencySymbol' => '$',
		];

		$response = $this->actingAs($this->systemUser)
			->post(route('settings.payment'), $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Payment setting successfully updated.'));
	}

	/**
	 ** @test
	 **
	 ** Authorized user can save system settings.
	 **/
	public function authorized_user_can_save_system_settings()
	{
		$data = [
			'siteCurrency' => 'EUR',
			'shippingDisplay' => 'on',
		];

		$response = $this->actingAs($this->companyUser)
			->post(route('settings.system'), $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Setting successfully updated.'));
	}

	/**
	 ** @test
	 **
	 ** Authorized user can save Zoom settings.
	 **/
	public function authorized_user_can_save_zoom_settings()
	{
		$data = [
			'zoomApiKey' => 'key',
			'zoomApiSecret' => 'secret',
		];

		$response = $this->actingAs($this->systemUser)
			->post(route('settings.zoom'), $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Setting successfully saved.'));
	}

	/**
	 ** @test
	 **
	 ** Authorized user can save business settings and upload company logos.
	 **/
	public function authorized_user_can_save_business_settings_with_uploads()
	{
		Storage::fake('uploads');
		$logo = UploadedFile::fake()->image('companyLogoDark.png');

		$response = $this->actingAs($this->systemUser)
			->post(route('settings.business'), [
				'companyLogoDark'  => $logo,
				'companyLogoLight' => $logo,
				'companyFavicon'   => $logo,
				'SITE_RTL'         => 'on',
				'custThemeBg'      => 'off',
				'custDarklayout'   => 'on',
			]);

		$response->assertRedirect()
			->assertSessionHas('success', __('Brand setting successfully updated.'));
	}
	/**
	 ** @test
	 **
	 ** Guests are redirected to login when accessing the webhook index.
	 **/
	public function guests_are_redirected_from_webhook_index()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'webhook']);
		$response = $this->get($url);

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users are redirected when accessing the webhook index.
	 **/
	public function unauthorized_users_cannot_access_webhook_index()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'webhook']);
		$response = $this->actingAs($this->unauthorizedUser)
			->get($url);

		$response->assertRedirect(route('settings.index'));
	}

	/**
	 ** @test
	 **
	 ** Authorized users can view the webhook index.
	 **/
	public function authorized_users_can_view_webhook_index()
	{
		WebhookSetting::factory()->count(2)->create([
			'created_by' => $this->authorizedUser->creatorId(),
		]);

		$url = action([\App\Http\Controllers\SystemController::class, 'webhook']);
		$response = $this->actingAs($this->authorizedUser)
			->get($url);

		$response->assertStatus(200)
			->assertViewIs('webhook.index')
			->assertViewHas('webhookSettings');
	}

	/**
	 ** @test
	 **
	 ** Guests receive 401 JSON when accessing the webhook create form.
	 **/
	public function guests_receive_401_on_webhook_create()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'webhookCreate']);
		$response = $this->getJson($url);

		$response->assertStatus(401)
			->assertJson(['error' => 'Unauthorized']);
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users are redirected when accessing the webhook create form.
	 **/
	public function unauthorized_users_cannot_access_webhook_create()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'webhookCreate']);
		$response = $this->actingAs($this->unauthorizedUser)
			->get($url);

		$response->assertRedirect(route('settings.index'));
	}

	/**
	 ** @test
	 **
	 ** Authorized users can view the webhook create form.
	 **/
	public function authorized_users_can_view_webhook_create_form()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'webhookCreate']);
		$response = $this->actingAs($this->authorizedUser)
			->get($url);

		$response->assertStatus(200)
			->assertViewIs('webhook.create')
			->assertViewHasAll(['modules', 'methods']);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can store a new webhook.
	 **/
	public function authorized_users_can_store_webhook()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'webhookStore']);
		$data = [
			'module' => 'test_module',
			'url'    => 'https://example.com/webhook',
			'method' => 'POST',
		];

		$response = $this->actingAs($this->authorizedUser)
			->post($url, $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Webhook successfully created.'));

		$this->assertDatabaseHas('webhook_settings', [
			'module'     => 'test_module',
			'url'        => 'https://example.com/webhook',
			'method'     => 'POST',
			'created_by' => $this->authorizedUser->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Guests receive 401 JSON when accessing the webhook edit form.
	 **/
	public function guests_receive_401_on_webhook_edit()
	{
		$webhook = WebhookSetting::factory()->create();
		$url = action([\App\Http\Controllers\SystemController::class, 'webhookEdit'], ['id' => $webhook->id]);
		$response = $this->getJson($url);

		$response->assertStatus(401)
			->assertJson(['error' => 'Unauthorized']);
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users are redirected when accessing the webhook edit form.
	 **/
	public function unauthorized_users_cannot_access_webhook_edit()
	{
		$webhook = WebhookSetting::factory()->create();
		$url = action([\App\Http\Controllers\SystemController::class, 'webhookEdit'], ['id' => $webhook->id]);
		$response = $this->actingAs($this->unauthorizedUser)
			->get($url);

		$response->assertRedirect(route('settings.index'));
	}

	/**
	 ** @test
	 **
	 ** Authorized users can view the webhook edit form.
	 **/
	public function authorized_users_can_view_webhook_edit_form()
	{
		$webhook = WebhookSetting::factory()->create([
			'created_by' => $this->authorizedUser->creatorId(),
		]);
		$url = action([\App\Http\Controllers\SystemController::class, 'webhookEdit'], ['id' => $webhook->id]);
		$response = $this->actingAs($this->authorizedUser)
			->get($url);

		$response->assertStatus(200)
			->assertViewIs('webhook.edit')
			->assertViewHasAll(['webhook', 'modules', 'methods']);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can update an existing webhook.
	 **/
	public function authorized_users_can_update_webhook()
	{
		$webhook = WebhookSetting::factory()->create([
			'created_by' => $this->authorizedUser->creatorId(),
			'url'        => 'https://old-url.test',
		]);
		$url = action([\App\Http\Controllers\SystemController::class, 'webhookUpdate'], ['id' => $webhook->id]);
		$data = [
			'module' => 'updated_module',
			'url'    => 'https://new-url.test',
			'method' => 'PUT',
		];

		$response = $this->actingAs($this->authorizedUser)
			->put($url, $data);

		$response->assertRedirect()
			->assertSessionHas('success', __('Webhook successfully updated.'));

		$this->assertDatabaseHas('webhook_settings', [
			'id'         => $webhook->id,
			'module'     => 'updated_module',
			'url'        => 'https://new-url.test',
			'method'     => 'PUT',
		]);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can delete a webhook.
	 **/
	public function authorized_users_can_delete_webhook()
	{
		$webhook = WebhookSetting::factory()->create([
			'created_by' => $this->authorizedUser->creatorId(),
		]);
		$url = action([\App\Http\Controllers\SystemController::class, 'webhookDestroy'], ['id' => $webhook->id]);

		$response = $this->actingAs($this->authorizedUser)
			->delete($url);

		$response->assertRedirect()
			->assertSessionHas('success', __('Webhook successfully deleted.'));

		$this->assertDatabaseMissing('webhook_settings', [
			'id' => $webhook->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected when viewing the test mail form.
	 **/
	public function guests_are_redirected_from_test_mail_form()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'testMail']);
		$response = $this->get($url);

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Authorized users can view the test mail form with data.
	 **/
	public function authorized_users_can_view_test_mail_form()
	{
		$data = ['mail_driver' => 'smtp'];
		$url = action([\App\Http\Controllers\SystemController::class, 'testMail']);
		$response = $this->actingAs($this->systemUser)
			->get($url, $data);

		$response->assertStatus(200)
			->assertViewIs('settings.test_mail')
			->assertViewHas('data', $data);
	}

	/**
	 ** @test
	 **
	 ** Guests receive 403 JSON when sending test email.
	 **/
	public function guests_receive_403_on_test_send_mail()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'testSendMail']);
		$response = $this->postJson($url, ['email' => 'foo@example.com']);

		$response->assertStatus(403)
			->assertJson(['success' => false, 'message' => 'Unauthorized']);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can send a test email successfully.
	 **/
	public function authorized_users_can_send_test_mail()
	{
		Mail::fake();
		$url = action([\App\Http\Controllers\SystemController::class, 'testSendMail']);
		$payload = [
			'email'            => 'user@example.com',
			'mail_driver'      => 'smtp',
			'mail_host'        => 'host',
			'mail_port'        => '587',
			'mail_username'    => 'user',
			'mail_password'    => 'secret',
			'mail_from_address' => 'from@example.com',
			'mail_from_name'   => 'From Name',
		];

		$response = $this->actingAs($this->systemUser)
			->postJson($url, $payload);

		$response->assertStatus(200)
			->assertJson(['success' => true, 'message' => __('Email sent successfully')]);
		Mail::assertSent(\App\Mail\TestMail::class);
	}

	/**
	 ** @test
	 **
	 ** Validation and storage of recaptcha settings when enabled.
	 **/
	public function recaptcha_settings_store_on()
	{
		Storage::fake();
		$url = action([\App\Http\Controllers\SystemController::class, 'recaptchaSettingStore']);
		$file = UploadedFile::fake()->create('recaptcha.json', 10, 'application/json');

		$response = $this->actingAs($this->systemUser)
			->post(route('settings.recaptcha'), [
				'recaptcha_module'            => 'on',
				'google_recaptcha_key'        => 'key123',
				'google_recaptcha_secret'     => 'secret123',
			]);

		$response->assertRedirect()
			->assertSessionHas('success', __('Recaptcha Settings updated successfully'));
	}

	/**
	 ** @test
	 **
	 ** Recaptcha settings store off when module disabled.
	 **/
	public function recaptcha_settings_store_off()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'recaptchaSettingStore']);

		$response = $this->actingAs($this->systemUser)
			->post(route('settings.recaptcha'), []);

		$response->assertRedirect()
			->assertSessionHas('success', __('Recaptcha Settings updated successfully'));
	}

	/**
	 ** @test
	 **
	 ** Cache clear commands run and redirect.
	 **/
	public function cache_setting_store_clears_and_redirects()
	{
		Artisan::shouldReceive('call')->with('cache:clear')->once();
		Artisan::shouldReceive('call')->with('optimize:clear')->once();

		$url = action([\App\Http\Controllers\SystemController::class, 'cacheSettingStore']);
		$response = $this->actingAs($this->systemUser)
			->post(route('settings.cache'));

		$response->assertRedirect()
			->assertSessionHas('success', __('Cache cleared successfully'));
	}

	/**
	 ** @test
	 **
	 ** Guests receive 403 JSON when saving footer note.
	 **/
	public function guests_receive_403_on_footer_note_store()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'footerNoteStore']);
		$response = $this->postJson($url, ['notes' => 'Test']);

		$response->assertStatus(403)
			->assertJson(['success' => false]);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can save footer note.
	 **/
	public function authorized_users_can_save_footer_note()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'footerNoteStore']);
		$response = $this->actingAs($this->systemUser)
			->postJson($url, ['notes' => 'Footer text']);

		$response->assertStatus(200)
			->assertJson(['is_success' => true, 'success' => __('Note successfully saved!')]);
		$this->assertDatabaseHas('settings', [
			'name'       => 'footerNotes',
			'value'      => 'Footer text',
			'created_by' => $this->systemUser->id,
		]);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can save tracker settings.
	 **/
	public function authorized_users_can_save_tracker_settings()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'saveTrackerSettings']);
		$response = $this->actingAs($this->systemUser)
			->post(route('settings.tracker'), ['interval_time' => '15']);

		$response->assertRedirect()
			->assertSessionHas('success', __('Time Tracker successfully updated.'));
		$this->assertDatabaseHas('settings', [
			'name'       => 'intervalTime',
			'value'      => '15',
			'created_by' => $this->systemUser->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Tracker settings validation fails without interval_time.
	 **/
	public function tracker_settings_validation_fails_without_interval()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'saveTrackerSettings']);
		$response = $this->actingAs($this->systemUser)
			->post(route('settings.tracker'), []);

		$response->assertSessionHasErrors('interval_time');
	}

	/**
	 ** @test
	 **
	 ** Authorized users can save ChatGPT settings.
	 **/
	public function authorized_users_can_save_chatgpt_settings()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'chatgptSetting']);
		$payload = ['chat_gpt_api_key' => 'abc123', 'chat_gpt_model' => 'gpt-4'];
		$response = $this->actingAs($this->systemUser)
			->post(route('settings.chatgpt'), $payload);

		$response->assertRedirect()
			->assertSessionHas('success', __('ChatGPT Setting successfully saved.'));
		$this->assertDatabaseHas('settings', [
			'name'       => 'chatGptApiKey',
			'value'      => 'abc123',
			'created_by' => $this->systemUser->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Guests are redirected from IP create form.
	 **/
	public function guests_are_redirected_from_create_ip()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'createIp']);
		$response = $this->get($url);

		$response->assertRedirect(route('login'));
	}

	/**
	 ** @test
	 **
	 ** Authorized users can view IP create form.
	 **/
	public function authorized_users_can_view_create_ip_form()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'createIp']);
		$response = $this->actingAs($this->companyUser)
			->get($url);

		$response->assertStatus(200)
			->assertViewIs('restrict_ip.create');
	}

	/**
	 ** @test
	 **
	 ** Authorized users can store a new IP restriction.
	 **/
	public function authorized_users_can_store_ip()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'storeIp']);
		$response = $this->actingAs($this->companyUser)
			->post(route('restrict_ip.store'), ['ip' => '123.123.123.123']);

		$response->assertRedirect()
			->assertSessionHas('success', __('IP successfully created.'));
		$this->assertDatabaseHas('ip_restricts', [
			'ip'         => '123.123.123.123',
			'created_by' => $this->companyUser->creatorId(),
		]);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can delete an IP restriction.
	 **/
	public function authorized_users_can_destroy_ip()
	{
		$ip = IpRestrict::factory()->create(['created_by' => $this->companyUser->creatorId()]);
		$url = action([\App\Http\Controllers\SystemController::class, 'destroyIp'], ['id' => $ip->id]);

		$response = $this->actingAs($this->companyUser)
			->delete($url);

		$response->assertRedirect()
			->assertSessionHas('success', __('IP successfully deleted.'));
		$this->assertDatabaseMissing('ip_restricts', ['id' => $ip->id]);
	}

	/**
	 ** @test
	 **
	 ** Local storage settings are validated, stored, and saved to DB.
	 **/
	public function local_storage_settings_are_saved()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'storageSettingStore']);

		$response = $this->actingAs($this->systemUser)
			->post($url, [
				'storage_setting'              => 'local',
				'local_storage_validation'     => ['jpg', 'png'],
				'local_storage_max_upload_size' => 2048,
			]);

		$response->assertRedirect()
			->assertSessionHas('success', __('Storage setting successfully updated.'));

		$this->assertDatabaseHas('settings', [
			'name'       => 'storageSetting',
			'value'      => 'local',
			'created_by' => $this->systemUser->creatorId(),
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 'localStorageValidation',
			'value' => 'jpg,png',
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 'localStorageMaxUploadSize',
			'value' => '2048',
		]);
	}

	/**
	 ** @test
	 **
	 ** S3 storage settings are validated, transformed, and saved to DB.
	 **/
	public function s3_storage_settings_are_saved()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'storageSettingStore']);

		$payload = [
			'storage_setting'        => 's3',
			's3_key'                 => 'key123',
			's3_secret'              => 'secret123',
			's3_region'              => 'us-west-1',
			's3_bucket'              => 'bucket-name',
			's3_url'                 => 'https://bucket.s3.amazonaws.com',
			's3_endpoint'            => 'https://s3.amazonaws.com',
			's3_max_upload_size'     => '4096',
			's3_storage_validation'  => ['pdf', 'docx'],
		];

		$response = $this->actingAs($this->systemUser)
			->post($url, $payload);

		$response->assertRedirect()
			->assertSessionHas('success', __('Storage setting successfully updated.'));

		$this->assertDatabaseHas('settings', [
			'name'  => 'storageSetting',
			'value' => 's3',
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 's3Key',
			'value' => 'key123',
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 's3StorageValidation',
			'value' => 'pdf,docx',
		]);
	}

	/**
	 ** @test
	 **
	 ** Google Calendar settings store with file upload when enabled.
	 **/
	public function google_calendar_settings_store_on()
	{
		Storage::fake('google_calendar');
		$json = UploadedFile::fake()->create('creds.json', 1, 'application/json');

		$url = action([\App\Http\Controllers\SystemController::class, 'saveGoogleCalendarSettings']);
		$response = $this->actingAs($this->systemUser)
			->post($url, [
				'google_calendar_enable'      => 'on',
				'google_calendar_json_file'   => $json,
				'google_clender_id'           => 'calendar-id',
			]);

		$response->assertRedirect()
			->assertSessionHas('success', __('Google Calendar setting successfully updated.'));

		$this->assertDatabaseHas('settings', [
			'name'  => 'google_calendar_enable',
			'value' => 'on',
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 'google_clender_id',
			'value' => 'calendar-id',
		]);
	}

	/**
	 ** @test
	 **
	 ** SEO settings store title, description, and image.
	 **/
	public function seo_settings_are_saved()
	{
		Storage::fake('uploads/meta');
		$image = UploadedFile::fake()->image('meta.png');

		$url = action([\App\Http\Controllers\SystemController::class, 'seoSettings']);
		$response = $this->actingAs($this->systemUser)
			->post($url, [
				'meta_title' => 'Title',
				'meta_desc'  => 'Description',
				'meta_image' => $image,
			]);

		$response->assertRedirect()
			->assertSessionHas('success', __('SEO setting successfully updated.'));

		$this->assertDatabaseHas('settings', [
			'name'  => 'meta_title',
			'value' => 'Title',
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 'meta_desc',
			'value' => 'Description',
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 'meta_image',
			'value' => 'meta.png',
		]);
	}

	/**
	 ** @test
	 **
	 ** Super admin can save Pusher settings; others are redirected.
	 **/
	public function pusher_settings_authorization_and_save()
	{
		$payload = [
			'pusher_app_id'      => 'id123',
			'pusher_app_key'     => 'key123',
			'pusher_app_secret'  => 'sec123',
			'pusher_app_cluster' => 'cluster123',
		];
		$url = action([\App\Http\Controllers\SystemController::class, 'savePusherSettings']);

		// Non-super-admin
		$normal = User::factory()->create(['type' => 'company']);
		$response = $this->actingAs($normal)->post($url, $payload);
		$response->assertRedirect(route('settings.index'));

		// Super admin
		$response2 = $this->actingAs($this->systemUser)->post($url, $payload);
		$response2->assertRedirect()
			->assertSessionHas('success', __('Pusher Settings updated successfully'));

		$this->assertDatabaseHas('settings', [
			'name'  => 'pusherAppId',
			'value' => 'id123',
		]);
	}

	/**
	 ** @test
	 **
	 ** Slack settings save webhook and flags.
	 **/
	public function slack_settings_are_saved()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'saveSlackSettings']);
		$payload = ['slack_webhook' => 'https://hooks.slack.com'];
		// include one flag
		$payload['lead_notification'] = 'on';

		$response = $this->actingAs($this->systemUser)->post($url, $payload);
		$response->assertRedirect()
			->assertSessionHas('success', __('Slack updated successfully.'));

		$this->assertDatabaseHas('settings', [
			'name'  => 'slackWebhook',
			'value' => 'https://hooks.slack.com',
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 'leadNotification',
			'value' => '1',
		]);
	}

	/**
	 ** @test
	 **
	 ** Telegram settings save tokens and flags.
	 **/
	public function telegram_settings_are_saved()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'saveTelegramSettings']);
		$payload = [
			'telegram_accestoken'            => 'token123',
			'telegram_chatid'                => 'chat123',
			'telegramLeadNotification'       => 'on',
		];

		$response = $this->actingAs($this->systemUser)->post($url, $payload);
		$response->assertRedirect()
			->assertSessionHas('success', __('Telegram updated successfully.'));

		$this->assertDatabaseHas('settings', [
			'name'  => 'telegramAccesToken',
			'value' => 'token123',
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 'telegramLeadNotification',
			'value' => '1',
		]);
	}

	/**
	 ** @test
	 **
	 ** Twilio settings save credentials and flags.
	 **/
	public function twilio_settings_are_saved()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'saveTwilioSettings']);
		$payload = [
			'twilio_sid'                    => 'sid123',
			'twilio_token'                  => 'tok123',
			'twilio_from'                   => 'from123',
			'twilioInvoiceNotification'     => 'on',
		];

		$response = $this->actingAs($this->systemUser)->post($url, $payload);
		$response->assertRedirect()
			->assertSessionHas('success', __('Twilio updated successfully.'));

		$this->assertDatabaseHas('settings', [
			'name'  => 'twilioSid',
			'value' => 'sid123',
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 'twilioInvoiceNotification',
			'value' => '1',
		]);
	}

	/**
	 ** @test
	 **
	 ** Cookie consent logs successfully when enabled.
	 **/
	public function cookie_consent_succeeds_when_enabled()
	{
		// Seed settings enableCookie and cookieLogging
		DB::table('settings')->insert([
			['name' => 'enableCookie', 'value' => 'on', 'created_by' => $this->systemUser->id],
			['name' => 'cookieLogging', 'value' => 'on', 'created_by' => $this->systemUser->id],
		]);

		File::ensureDirectoryExists(storage_path('uploads/sample'));
		$url = action([\App\Http\Controllers\SystemController::class, 'cookieConsent']);
		$response = $this->postJson($url, ['cookie' => ['necessary', 'analytics']]);

		$response->assertStatus(200)
			->assertJson('success');
	}


	/**
	 ** @test
	 **
	 ** Authorized company user can save general company settings.
	 **/
	public function authorized_company_user_can_save_company_settings()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'saveCompanySettings']);

		$response = $this->actingAs($this->companyUser)
			->post($url, [
				'companyName'          => 'Acme Corp',
				'vatGstNumberSwitch'   => 'on',
				'ipRestrict'           => 'on',
			]);

		$response->assertRedirect()
			->assertSessionHas('success', __('Setting successfully updated.'));

		$this->assertDatabaseHas('settings', [
			'name'       => 'companyName',
			'value'      => 'Acme Corp',
			'created_by' => $this->companyUser->creatorId(),
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 'vatGstNumberSwitch',
			'value' => 'on',
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 'ipRestrict',
			'value' => 'on',
		]);
	}

	/**
	 ** @test
	 **
	 ** Unauthorized user cannot save company settings.
	 **/
	public function unauthorized_user_cannot_save_company_settings()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'saveCompanySettings']);

		$response = $this->actingAs($this->systemUser)
			->post($url, ['companyName' => 'Foo']);

		// guard redirects back to settings.index
		$response->assertRedirect(route('settings.index'));
	}

	/**
	 ** @test
	 **
	 ** Authorized company user can save company payment settings.
	 **/
	public function authorized_company_user_can_save_company_payment_settings()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'saveCompanyPaymentSettings']);

		$response = $this->actingAs($this->companyUser)
			->post($url, [
				'is_stripe_enabled'  => 'on',
				'stripe_key'         => 'sk_test',
				'stripe_secret'      => 'secret_test',
			]);

		$response->assertRedirect()
			->assertSessionHas('success', __('Payment setting successfully updated.'));

		$this->assertDatabaseHas('company_payment_settings', [
			'name'       => 'is_stripe_enabled',
			'value'      => 'on',
			'created_by' => $this->companyUser->id,
		]);
		$this->assertDatabaseHas('company_payment_settings', [
			'name'  => 'stripe_key',
			'value' => 'sk_test',
		]);
		$this->assertDatabaseHas('company_payment_settings', [
			'name'  => 'stripe_secret',
			'value' => 'secret_test',
		]);
	}

	/**
	 ** @test
	 **
	 ** Authorized user can call adminPaymentSettings and persist settings.
	 **/
	public function authorized_user_can_call_admin_payment_settings()
	{
		$url = action([\App\Http\Controllers\SystemController::class, 'adminPaymentSettings']);

		$response = $this->actingAs($this->systemUser)
			->post($url, [
				'currency'         => 'USD',
				'currency_symbol'  => '$',
				'is_stripe_enabled' => 'on',
				'stripe_key'       => 'sk_live',
				'stripe_secret'    => 'secret_live',
			]);

		// Method returns void → HTTP 200 OK
		$response->assertStatus(200);

		$this->assertDatabaseHas('admin_payment_settings', [
			'name'       => 'currency',
			'value'      => 'USD',
			'created_by' => $this->systemUser->creatorId(),
		]);
		$this->assertDatabaseHas('admin_payment_settings', [
			'name'  => 'currencySymbol',
			'value' => '$',
		]);
		$this->assertDatabaseHas('admin_payment_settings', [
			'name'  => 'is_stripe_enabled',
			'value' => 'on',
		]);
		$this->assertDatabaseHas('admin_payment_settings', [
			'name'  => 'stripe_key',
			'value' => 'sk_live',
		]);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can view the IP edit form.
	 **/
	public function authorized_users_can_view_ip_edit_form()
	{
		$ip = IpRestrict::factory()->create([
			'created_by' => $this->companyUser->creatorId(),
		]);

		$url = action([\App\Http\Controllers\SystemController::class, 'editIp'], ['id' => $ip->id]);
		$response = $this->actingAs($this->companyUser)->get($url);

		$response->assertStatus(200)
			->assertViewIs('restrict_ip.edit')
			->assertViewHas('ip', $ip);
	}

	/**
	 ** @test
	 **
	 ** Authorized users can update an IP restriction.
	 **/
	public function authorized_users_can_update_ip_restriction()
	{
		$ip = IpRestrict::factory()->create([
			'created_by' => $this->companyUser->creatorId(),
			'ip'         => '1.1.1.1',
		]);

		$url = action([\App\Http\Controllers\SystemController::class, 'updateIp'], ['id' => $ip->id]);
		$response = $this->actingAs($this->companyUser)
			->put($url, ['ip' => '2.2.2.2']);

		$response->assertRedirect()
			->assertSessionHas('success', __('IP successfully updated.'));

		$this->assertDatabaseHas('ip_restricts', [
			'id' => $ip->id,
			'ip' => '2.2.2.2',
		]);
	}

	/**
	 ** @test
	 **
	 ** Unauthorized users cannot edit or update IP restrictions.
	 **/
	public function unauthorized_users_cannot_edit_or_update_ip()
	{
		$ip = IpRestrict::factory()->create([
			'created_by' => $this->companyUser->creatorId(),
		]);

		$editUrl  = action([\App\Http\Controllers\SystemController::class, 'editIp'],   ['id' => $ip->id]);
		$updateUrl = action([\App\Http\Controllers\SystemController::class, 'updateIp'], ['id' => $ip->id]);

		$response1 = $this->actingAs($this->systemUser)->get($editUrl);
		$response2 = $this->actingAs($this->systemUser)->put($updateUrl, ['ip' => '3.3.3.3']);

		// Guard redirects back to settings.index
		$response1->assertRedirect(route('settings.index'));
		$response2->assertRedirect(route('settings.index'));
	}


	/**
	 ** @test
	 **
	 ** cookieConsent returns 400 when cookie logging is disabled.
	 **/
	public function cookie_consent_returns_400_when_disabled()
	{
		// No settings or cookieLogging off by default
		$response = $this->postJson(route('settings.cookieConsent'), ['cookie' => ['necessary']]);

		$response->assertStatus(400)
			->assertJson(['error']);
	}

	/**
	 ** @test
	 **
	 ** Wasabi storage settings are validated, transformed, and saved.
	 **/
	public function wasabi_storage_settings_are_saved()
	{
		$payload = [
			'storage_setting'             => 'wasabi',
			'wasabi_key'                  => 'key1',
			'wasabi_secret'               => 'sec1',
			'wasabi_region'               => 'reg1',
			'wasabi_bucket'               => 'buck1',
			'wasabi_url'                  => 'url1',
			'wasabi_root'                 => 'root1',
			'wasabi_max_upload_size'      => '2048',
			'wasabi_storage_validation'   => ['jpg', 'png'],
		];

		$response = $this->actingAs($this->user)
			->post(route('settings.storage'), $payload);

		$response->assertRedirect()
			->assertSessionHas('success', __('Storage setting successfully updated.'));

		$this->assertDatabaseHas('settings', [
			'name'  => 'storageSetting',
			'value' => 'wasabi',
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 'wasabiKey',
			'value' => 'key1',
		]);
		$this->assertDatabaseHas('settings', [
			'name'  => 'wasabiStorageValidation',
			'value' => 'jpg,png',
		]);
	}
}
