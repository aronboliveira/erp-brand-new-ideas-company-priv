<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

trait ApiResponser
{
	protected function success(mixed $data, ?string $message = null, int $code = 200): JsonResponse
	{
		Log::info(__METHOD__ . ' invoked', ['code' => $code]);
		if ($data === null || (is_array($data) && empty($data)) || (is_string($data) && $data === '')) {
			Log::warning(__METHOD__ . ' returned nullish or empty data', ['data' => $data]);
		}
		return response()->json([
			'is_success' => true,
			'message' => $message,
			'data' => $data
		], $code);
	}

	protected function error(string $message, int $code, mixed $data = null): JsonResponse
	{
		Log::info(__METHOD__ . ' invoked', ['code' => $code]);
		if ($data === null || (is_array($data) && empty($data)) || (is_string($data) && $data === '')) {
			Log::warning(__METHOD__ . ' returned nullish or empty error data', ['data' => $data]);
		}
		return response()->json([
			'is_success' => false,
			'message' => $message,
			'data' => $data
		], $code);
	}
}
