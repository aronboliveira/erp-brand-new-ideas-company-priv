<?php

use Tests\TestCase;
use Illuminate\{
	Foundation\Testing\RefreshDatabase,
	Support\Str
};
use App\Models\WebhookSettings as WebhookSetting;

use Illuminate\Support\Facades\DB;
class WebhookSettingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
    }
	use RefreshDatabase;

	/**
	 ** @test
	 **
	 ** WebhookSetting is fillable for module, url, method, and created_by
	 **/
	public function webhook_setting_is_fillable()
	{
		$data = [
			'module'     => 'new invoice',
			'url'        => 'https://example.com/hook',
			'method'     => 'post',
			'created_by' => Str::uuid()->toString(),
		];

		$ws = WebhookSetting::create($data);

		$this->assertFillableMatches($data, $ws);
	}

	/**
	 ** @test
	 **
	 ** Uses UUID for primary key: string, non-incrementing, valid UUID
	 **/
	public function webhook_setting_primary_key_is_uuid()
	{
		$ws = WebhookSetting::factory()->create();
		$key = $ws->getKey();

		$this->assertIsString($key);
		$this->assertFalse($ws->getIncrementing());
		$this->assertSame('string', $ws->getKeyType());
		$this->assertMatchesRegularExpression(
			'/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
			$key
		);
	}

	/**
	 ** @test
	 **
	 ** static modules and method arrays contain expected keys
	 **/
	public function static_arrays_are_correct()
	{
		$expectedModules = [
			'new lead'                => 'New Lead',
			'lead to deal conversion' => 'Lead to Deal Conversion',
			'new project'             => 'New Project',
			'task stage updated'      => 'Task Stage Updated',
			'new deal'                => 'New Deal',
			'new contract'            => 'New Contract',
			'new task'                => 'New Task',
			'new task comment'        => 'New Task Comment',
			'new monthly payslip'     => 'New Monthly Payslip',
			'new announcement'        => 'New Announcement',
			'new support ticket'      => 'New Support Ticket',
			'new meeting'             => 'New Meeting',
			'new award'               => 'New Award',
			'new holiday'             => 'New Holiday',
			'new event'               => 'New Event',
			'new company policy'      => 'New Company Policy',
			'new invoice'             => 'New Invoice',
			'new bill'                => 'New Bill',
			'new budget'              => 'New Budget',
			'new revenue'             => 'New Revenue',
			'new invoice payment'     => 'New Invoice Payment',
		];

		$expectedMethods = ['get' => 'GET', 'post' => 'POST'];

		$this->assertSame($expectedModules, WebhookSetting::$modules);
		$this->assertSame($expectedMethods, WebhookSetting::$method);
	}
}
