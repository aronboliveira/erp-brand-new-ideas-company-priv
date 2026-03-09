<?php

declare(strict_types=1);

namespace Tests\Unit\app\Traits;

use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\Attributes\{CoversClass, DataProvider, Group, Test};
use Tests\TestCase;

#[Group('traits')]
class ApiResponserTest extends TestCase
{
	use ApiResponser {
		success as public traitSuccess;
		error as public traitError;
	}

	/* ══════════════════════ success() ══════════════════════ */

	#[Test]
	public function success_returns_json_response(): void
	{
		$response = $this->traitSuccess(['key' => 'value']);
		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertSame(200, $response->getStatusCode());
	}

	#[Test]
	public function success_contains_correct_structure(): void
	{
		$response = $this->traitSuccess(['items' => [1, 2, 3]], 'All good', 201);
		$data = $response->getData(true);
		$this->assertTrue($data['is_success']);
		$this->assertSame('All good', $data['message']);
		$this->assertSame(['items' => [1, 2, 3]], $data['data']);
		$this->assertSame(201, $response->getStatusCode());
	}

	#[Test]
	public function success_handles_null_data(): void
	{
		$response = $this->traitSuccess(null);
		$data = $response->getData(true);
		$this->assertTrue($data['is_success']);
		$this->assertNull($data['data']);
	}

	#[Test]
	public function success_handles_empty_array(): void
	{
		$response = $this->traitSuccess([]);
		$data = $response->getData(true);
		$this->assertTrue($data['is_success']);
		$this->assertSame([], $data['data']);
	}

	#[Test]
	public function success_handles_empty_string(): void
	{
		$response = $this->traitSuccess('');
		$data = $response->getData(true);
		$this->assertTrue($data['is_success']);
		$this->assertSame('', $data['data']);
	}

	#[Test]
	public function success_handles_nested_array(): void
	{
		$nested = ['a' => ['b' => ['c' => 'deep']]];
		$response = $this->traitSuccess($nested);
		$data = $response->getData(true);
		$this->assertSame('deep', $data['data']['a']['b']['c']);
	}

	#[Test]
	public function success_with_null_message(): void
	{
		$response = $this->traitSuccess('ok', null);
		$data = $response->getData(true);
		$this->assertNull($data['message']);
	}

	/* ══════════════════════ error() ══════════════════════ */

	#[Test]
	public function error_returns_json_response(): void
	{
		$response = $this->traitError('Something went wrong', 500);
		$this->assertInstanceOf(JsonResponse::class, $response);
		$this->assertSame(500, $response->getStatusCode());
	}

	#[Test]
	public function error_contains_correct_structure(): void
	{
		$response = $this->traitError('Not found', 404, ['id' => 42]);
		$data = $response->getData(true);
		$this->assertFalse($data['is_success']);
		$this->assertSame('Not found', $data['message']);
		$this->assertSame(['id' => 42], $data['data']);
	}

	#[Test]
	public function error_handles_null_data(): void
	{
		$response = $this->traitError('err', 400);
		$data = $response->getData(true);
		$this->assertFalse($data['is_success']);
		$this->assertNull($data['data']);
	}

	#[Test]
	#[DataProvider('httpCodesProvider')]
	public function success_and_error_respect_status_codes(int $code): void
	{
		$s = $this->traitSuccess('ok', null, $code);
		$this->assertSame($code, $s->getStatusCode());
		$e = $this->traitError('err', $code);
		$this->assertSame($code, $e->getStatusCode());
	}

	public static function httpCodesProvider(): array
	{
		return [
			'200' => [200],
			'201' => [201],
			'204' => [204],
			'400' => [400],
			'401' => [401],
			'403' => [403],
			'404' => [404],
			'422' => [422],
			'500' => [500],
			'503' => [503],
		];
	}

	/* ══════════════════════ performance ══════════════════════ */

	#[Test]
	public function success_call_performance(): void
	{
		$start = hrtime(true);
		for ($i = 0; $i < 500; $i++) {
			$this->traitSuccess(['i' => $i], 'msg', 200);
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(500, $elapsed, '500 success() calls should be < 500ms');
	}

	#[Test]
	public function error_call_performance(): void
	{
		$start = hrtime(true);
		for ($i = 0; $i < 500; $i++) {
			$this->traitError('err', 500, ['i' => $i]);
		}
		$elapsed = (hrtime(true) - $start) / 1e6;
		$this->assertLessThan(500, $elapsed, '500 error() calls should be < 500ms');
	}
}
