<?php

namespace Tests\Unit\Models;

use App\Models\FormResponse;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
class FormResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	/**
	 ** @test
	 *
	 ** The $fillable array must match the declared fields.
	 **/
	public function fillable_array_matches_constant(): void
	{
		$expected = [
			'form_id',
			'submitted_at',
			'submitted_by',
			'submission_email',
			'submission_ip',
			'submission_user_agent',
			'submission_data_url',
			'response',
			'status',
			'captcha_approved',
			'consent_checked',
			'csrf_token_approved',
			'is_malware_free',
			'expires_at',
			'lakes',
			'edits',
			'metadata',
		];

		$this->assertSame($expected, (new FormResponse)->getFillable());
	}

	/**
	 ** @test
	 *
	 ** form() relation must be a BelongsTo mapping
	 ** forms.id ← form_responses.form_id.
	 **/
	public function form_relation_is_belongs_to(): void
	{
		$rel = (new FormResponse)->form();

		$this->assertInstanceOf(
			\Illuminate\Database\Eloquent\Relations\BelongsTo::class,
			$rel
		);
		$this->assertSame('form_id', $rel->getForeignKeyName());
		$this->assertSame('id',      $rel->getOwnerKeyName());
	}
}
