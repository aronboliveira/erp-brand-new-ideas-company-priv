<?php

declare(strict_types=1);

namespace Tests\Unit\app\Models\Traits;

use App\Traits\LogsIcons;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

use Illuminate\Support\Facades\DB;
#[Group('models-traits')]
class LogsIconsTest extends TestCase
{
	private static function host(): object
	{
		return new class {
			use LogsIcons;
			public ?string $log_type = null;
		};
	}

	protected function setUp(): void
	{
		parent::setUp();
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
		// Reset static icon map cache between tests
		$ref = new \ReflectionClass(self::host());
		$prop = $ref->getProperty('iconMap');
		$prop->setValue(null, null);
	}
	/* ══════════════ logIcon ══════════════ */

	#[Test]
	public function log_icon_returns_icon_for_add_contact(): void
	{
		$h = self::host();
		$h->log_type = 'Add Contact';
		$this->assertSame('ti-notebook', $h->logIcon());
	}

	#[Test]
	public function log_icon_returns_icon_for_add_product(): void
	{
		$h = self::host();
		$h->log_type = 'Add Product';
		$this->assertSame('ti-shopping-cart-plus', $h->logIcon());
	}

	#[Test]
	public function log_icon_returns_icon_for_move(): void
	{
		$h = self::host();
		$h->log_type = 'Move';
		$this->assertSame('ti-arrows-maximize', $h->logIcon());
	}

	#[Test]
	public function log_icon_returns_icon_for_move_task(): void
	{
		$h = self::host();
		$h->log_type = 'Move Task';
		$this->assertSame('ti-command', $h->logIcon());
	}

	#[Test]
	public function log_icon_returns_icon_for_user_assigned(): void
	{
		$h = self::host();
		$h->log_type = 'User Assigned to the Task';
		$this->assertSame('ti-user-check', $h->logIcon());
	}

	#[Test]
	public function log_icon_returns_icon_for_user_removed(): void
	{
		$h = self::host();
		$h->log_type = 'User Removed from the Task';
		$this->assertSame('ti-user-x', $h->logIcon());
	}

	#[Test]
	public function log_icon_returns_empty_for_unknown(): void
	{
		$h = self::host();
		$h->log_type = 'Unknown Event';
		$this->assertSame('', $h->logIcon());
	}

	#[Test]
	public function log_icon_returns_empty_for_null(): void
	{
		$h = self::host();
		$h->log_type = null;
		$this->assertSame('', $h->logIcon());
	}

	/* ══════════════ performance ══════════════ */

	#[Test]
	public function log_icon_caching_performance(): void
	{
		$h = self::host();
		$h->log_type = 'Add Contact';
		$start = hrtime(true);
		for ($i = 0; $i < 10000; $i++) {
			$h->logIcon();
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(500, $elapsed, '10000 logIcon should be < 500ms (cached)');
	}
}
