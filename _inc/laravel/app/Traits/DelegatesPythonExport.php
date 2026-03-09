<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use RuntimeException;

trait DelegatesPythonExport
{
	private const PYTHON_SCRIPTS_PATH = __DIR__ . '/../Exports/py/';
	private const PYTHON_BINARY       = 'python3';
	private const PROC_TIMEOUT        = 60;

	protected static function _executePythonExporter(
		string $exporterName,
		array $data,
		?string $outputPath = null
	): string {
		$result ??= '';
		$scriptPath ??= '';
		$jsonInput ??= '';
		$process ??= null;
		$pipes ??= [];
		$stdout ??= '';
		$stderr ??= '';
		$exitCode ??= -1;
		try {
			$scriptPath = self::_resolvePythonScript($exporterName);
			if (empty($scriptPath) || !file_exists($scriptPath)) {
				Log::error(__METHOD__ . ' script not found', [
					'exporter' => $exporterName,
					'path' => $scriptPath,
					'class' => static::class
				]);
				throw new RuntimeException(
					"Python script not found: {$exporterName}"
				);
			}
			if (!empty($outputPath)) {
				$data['output_path'] = $outputPath;
			}
			$jsonInput = json_encode($data, JSON_THROW_ON_ERROR);
			if (empty($jsonInput)) {
				Log::error(__METHOD__ . ' JSON encode failed', [
					'exporter' => $exporterName,
					'class' => static::class
				]);
				throw new RuntimeException('Failed to encode data as JSON');
			}
			Log::info(__METHOD__ . ' executing', [
				'exporter' => $exporterName,
				'script' => $scriptPath,
				'data_size' => strlen($jsonInput),
				'class' => static::class
			]);
			$descriptors = [
				0 => ['pipe', 'r'],
				1 => ['pipe', 'w'],
				2 => ['pipe', 'w'],
			];
			$pythonPath = self::_resolvePythonBinary();
			$command = escapeshellarg($pythonPath) . ' ' . escapeshellarg($scriptPath);
			$process = proc_open(
				$command,
				$descriptors,
				$pipes,
				dirname($scriptPath),
				null
			);
			if (!is_resource($process)) {
				Log::error(__METHOD__ . ' proc_open failed', [
					'exporter' => $exporterName,
					'command' => $command,
					'class' => static::class
				]);
				throw new RuntimeException(
					"Failed to start Python process for {$exporterName}"
				);
			}
			stream_set_blocking($pipes[0], false);
			stream_set_blocking($pipes[1], false);
			stream_set_blocking($pipes[2], false);
			$written = fwrite($pipes[0], $jsonInput);
			fclose($pipes[0]);
			if ($written === false || $written !== strlen($jsonInput)) {
				Log::warning(__METHOD__ . ' incomplete stdin write', [
					'exporter' => $exporterName,
					'expected' => strlen($jsonInput),
					'written' => $written,
					'class' => static::class
				]);
			}
			$startTime = time();
			$stdout = '';
			$stderr = '';
			while (true) {
				$status = proc_get_status($process);
				if (!is_array($status)) {
					break;
				}
				$stdoutChunk = stream_get_contents($pipes[1]);
				$stderrChunk = stream_get_contents($pipes[2]);
				if (is_string($stdoutChunk)) {
					$stdout .= $stdoutChunk;
				}
				if (is_string($stderrChunk)) {
					$stderr .= $stderrChunk;
				}
				if (!($status['running'] ?? false)) {
					$exitCode = $status['exitcode'] ?? -1;
					break;
				}
				if ((time() - $startTime) > self::PROC_TIMEOUT) {
					Log::error(__METHOD__ . ' timeout', [
						'exporter' => $exporterName,
						'timeout' => self::PROC_TIMEOUT,
						'class' => static::class
					]);
					foreach ([1, 2] as $pipeIdx) {
						if (isset($pipes[$pipeIdx]) && is_resource($pipes[$pipeIdx])) fclose($pipes[$pipeIdx]);
					}
					proc_terminate($process, 9);
					proc_close($process);
					throw new RuntimeException(
						"Python export timeout for {$exporterName}"
					);
				}
				usleep(10000);
			}
			$finalStdout = stream_get_contents($pipes[1]);
			$finalStderr = stream_get_contents($pipes[2]);
			if (is_string($finalStdout)) {
				$stdout .= $finalStdout;
			}
			if (is_string($finalStderr)) {
				$stderr .= $finalStderr;
			}
			fclose($pipes[1]);
			fclose($pipes[2]);
			proc_close($process);
			if (!empty($stderr)) {
				Log::info(__METHOD__ . ' stderr output', [
					'exporter' => $exporterName,
					'stderr' => substr($stderr, 0, 2000),
					'class' => static::class
				]);
			}
			if ($exitCode !== 0) {
				Log::error(__METHOD__ . ' non-zero exit', [
					'exporter' => $exporterName,
					'exit_code' => $exitCode,
					'stderr' => substr($stderr, 0, 1000),
					'class' => static::class
				]);
				throw new RuntimeException(
					"Python export failed with code {$exitCode}: " .
						substr($stderr, 0, 500)
				);
			}
			if (!empty($outputPath) && file_exists($outputPath)) {
				$result = $outputPath;
				Log::info(__METHOD__ . ' file output success', [
					'exporter' => $exporterName,
					'path' => $outputPath,
					'size' => filesize($outputPath),
					'class' => static::class
				]);
			} else {
				$result = $stdout;
				Log::info(__METHOD__ . ' stdout output success', [
					'exporter' => $exporterName,
					'size' => strlen($stdout),
					'class' => static::class
				]);
			}
		} catch (\JsonException $e) {
			Log::error(__METHOD__ . ' JSON error', [
				'exporter' => $exporterName,
				'error' => $e->getMessage(),
				'class' => static::class
			]);
			$result = '';
		} catch (RuntimeException $e) {
			Log::error(__METHOD__ . ' runtime error', [
				'exporter' => $exporterName,
				'error' => $e->getMessage(),
				'class' => static::class
			]);
			$result = '';
		} catch (\Throwable $e) {
			Log::error(__METHOD__ . ' unexpected error', [
				'exporter' => $exporterName,
				'error_class' => get_class($e),
				'error' => $e->getMessage(),
				'class' => static::class
			]);
			$result = '';
		}
		return $result;
	}

	protected static function _resolvePythonScript(string $exporterName): string
	{
		$scriptName ??= '';
		$scriptPath ??= '';
		try {
			$scriptName = self::_exporterToScriptName($exporterName);
			$scriptPath = realpath(self::PYTHON_SCRIPTS_PATH . $scriptName);
			if ($scriptPath === false) {
				$scriptPath = self::PYTHON_SCRIPTS_PATH . $scriptName;
			}
		} catch (\Throwable $e) {
			Log::error(__METHOD__ . ' resolution failed', [
				'exporter' => $exporterName,
				'error' => $e->getMessage()
			]);
			$scriptPath = '';
		}
		return is_string($scriptPath) ? $scriptPath : '';
	}

	protected static function _exporterToScriptName(string $exporterName): string
	{
		$result ??= '';
		try {
			$snake = strtolower(preg_replace(
				'/([a-z])([A-Z])/',
				'$1_$2',
				str_replace('Export', '', $exporterName)
			) ?? '');
			$result = $snake . '_exporter.py';
		} catch (\Throwable $e) {
			Log::error(__METHOD__ . ' name conversion failed', [
				'exporter' => $exporterName,
				'error' => $e->getMessage()
			]);
			$result = '';
		}
		return $result;
	}

	protected static function _resolvePythonBinary(): string
	{
		$binary ??= self::PYTHON_BINARY;
		try {
			$configBinary = config('exports.python_binary');
			if (!empty($configBinary) && is_string($configBinary)) {
				$binary = $configBinary;
			}
			$envBinary = env('PYTHON_BINARY');
			if (!empty($envBinary) && is_string($envBinary)) {
				$binary = $envBinary;
			}
		} catch (\Throwable $e) {
			Log::warning(__METHOD__ . ' config resolution failed', [
				'error' => $e->getMessage()
			]);
		}
		return $binary;
	}

	protected static function _generateOutputPath(string $prefix, string $extension = 'xlsx'): string
	{
		$path ??= '';
		try {
			$timestamp = date('Y-m-d_His');
			$filename = "{$prefix}_{$timestamp}.{$extension}";
			$dir = storage_path('exports');
			if (!is_dir($dir)) {
				mkdir($dir, 0755, true);
			}
			$path = $dir . DIRECTORY_SEPARATOR . $filename;
		} catch (\Throwable $e) {
			Log::error(__METHOD__ . ' path generation failed', [
				'prefix' => $prefix,
				'error' => $e->getMessage()
			]);
			$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR .
				"{$prefix}_" . time() . ".{$extension}";
		}
		return $path;
	}

	protected static function _prepareCurrencySymbol($user): string
	{
		$symbol ??= '';
		try {
			if (is_object($user) && method_exists($user, 'currencySymbol')) {
				$symbol = $user->currencySymbol() ?? '';
			}
			if (empty($symbol) && is_object($user)) {
				$symbol = $user->currency_symbol ?? '';
			}
		} catch (\Throwable $e) {
			Log::warning(__METHOD__ . ' currency symbol retrieval failed', [
				'error' => $e->getMessage()
			]);
		}
		return is_string($symbol) ? $symbol : '';
	}
}
