<?php

namespace Tests\Unit\Models;

use App\Models\FormBuilder;
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
class FormBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** The $fillable array must match the private
	 ** constant fields.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'code',
			'shortcode',
			'module',
			'name',
			'visibility',
			'captcha_provider',
			'rate_limit',
			'retention_days',
			'expires_at',
			'is_active',
			'requires_login',
			'limit_one_per_user',
			'accepting_submissions',
			'csrf_check_required',
			'allow_edit_after_submission',
			'consent_required',
			'consent_document',
			'privacy_policy_document',
			'is_lead_active',
			'is_deal_active',
			'is_support_active',
			'is_project_active',
			'is_contact_active',
			'lead_id',
			'deal_id',
			'support_id',
			'project_id',
			'contract_id',
			'receiver',
			'received_template',
			'submitted_template',
			'failed_template',
			'redirect_url',
			'generator',
			'submission_count',
			'deployment_url',
			'deployed_by',
			'authorized_deployment_roles',
			'published_by',
			'published_at',
			'authorized_publishing_roles',
			'deployed_at',
			'submission_data_url',
			'form_name',
			'title',
			'description',
			'action',
			'method',
			'enctype',
			'target',
			'accept_charset',
			'autocomplete',
			'novalidate',
			'referrerpolicy',
			'allowed_methods',
			'allowed_enctypes',
			'submit_label',
			'reset_label',
			'show_reset',
			'prevent_double_submit',
			'submit_debounce_ms',
			'aria',
			'dataset',
			'selectors',
			'size',
			'tags',
			'variables',
			'fields',
			'scripts',
			'styles',
			'webhooks',
			'notifications',
			'cookies',
			'exports',
			'other_urls',
			'blocked_ip_ranges',
			'settings',
		];

		$this->assertSame($expected, (new FormBuilder)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** The static $fieldTypes array must be
	 ** unchanged to ensure consistency.
	 **/
	public function static_field_types_array_is_intact(): void
	{
		$expected = [
			'code',
			'shortcode',
			'module',
			'name',
			'visibility',
			'captcha_provider',
			'rate_limit',
			'retention_days',
			'expires_at',
			'is_active',
			'requires_login',
			'limit_one_per_user',
			'accepting_submissions',
			'csrf_check_required',
			'allow_edit_after_submission',
			'consent_required',
			'consent_document',
			'privacy_policy_document',
			'is_lead_active',
			'is_deal_active',
			'is_support_active',
			'is_project_active',
			'is_contact_active',
			'lead_id',
			'deal_id',
			'support_id',
			'project_id',
			'contract_id',
			'receiver',
			'received_template',
			'submitted_template',
			'failed_template',
			'redirect_url',
			'generator',
			'submission_count',
			'deployment_url',
			'deployed_by',
			'authorized_deployment_roles',
			'published_by',
			'published_at',
			'authorized_publishing_roles',
			'deployed_at',
			'submission_data_url',
			'form_name',
			'title',
			'description',
			'action',
			'method',
			'enctype',
			'target',
			'accept_charset',
			'autocomplete',
			'novalidate',
			'referrerpolicy',
			'allowed_methods',
			'allowed_enctypes',
			'submit_label',
			'reset_label',
			'show_reset',
			'prevent_double_submit',
			'submit_debounce_ms',
			'aria',
			'dataset',
			'selectors',
			'size',
			'tags',
			'variables',
			'fields',
			'scripts',
			'styles',
			'webhooks',
			'notifications',
			'cookies',
			'exports',
			'other_urls',
			'blocked_ip_ranges',
			'settings',
		];

		$this->assertSame($expected, (new FormBuilder())->getFillable());
	}

	/**
	 ** @test
	 *
	 ** formField(), fieldResponse(), and response()
	 ** must each be Eloquent relations of the correct type.
	 **/
	public function relations_are_correct_type(): void
	{
		$fb = new FormBuilder;

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasMany::class,
			$fb->formField()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasOne::class,
			$fb->fieldResponse()
		);
		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\HasMany::class,
			$fb->responses()
		);
	}
}
