<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Trait DelegatesPythonImport
 *
 * Provides proc_open-based execution of Python importer scripts.
 * Sends JSON data via stdin to the Python process and receives
 * the validated result as JSON via stdout.
 *
 * @package App\Traits
 */
trait DelegatesPythonImport
{
	private const PYTHON_IMPORT_SCRIPTS_PATH = __DIR__ . '/../Imports/py/';
	private const PYTHON_IMPORT_BINARY       = 'python3';
	private const PYTHON_IMPORT_TIMEOUT      = 120;

	/**
	 * Public convenience wrapper for _executePythonImporter().
	 *
	 * @param array $data Payload to send to the Python process
	 * @return array Decoded JSON response
	 */
	public function importViaPython(array $data): array
	{
		$name = defined('static::PYTHON_IMPORTER')
			? static::PYTHON_IMPORTER
			: class_basename(static::class);
		return static::_executePythonImporter($name, $data);
	}

	/**
	 * Execute a Python import script via proc_open.
	 *
	 * @param string      $importerName  PascalCase importer class name (e.g. 'AttendanceImport')
	 * @param array       $data          Payload to JSON-encode and send via stdin
	 * @return array      Decoded JSON response from the Python process
	 */
	protected static function _executePythonImporter(
		string $importerName,
		array $data
	): array {
		$result ??= [];
		$scriptPath ??= '';
		$jsonInput ??= '';
		$process ??= null;
		$pipes ??= [];
		$stdout ??= '';
		$stderr ??= '';
		$exitCode ??= -1;
		try {
			$scriptPath = self::_resolveImportPythonScript($importerName);
			if (empty($scriptPath) || !file_exists($scriptPath)) {
				Log::error(__METHOD__ . ' script not found', [
					'importer' => $importerName,
					'path' => $scriptPath,
					'class' => static::class
				]);
				throw new RuntimeException(
					"Python import script not found: {$importerName}"
				);
			}
			$jsonInput = json_encode($data, JSON_THROW_ON_ERROR);
			if (empty($jsonInput)) {
				Log::error(__METHOD__ . ' JSON encode failed', [
					'importer' => $importerName,
					'class' => static::class
				]);
				throw new RuntimeException('Failed to encode import data as JSON');
			}
			Log::info(__METHOD__ . ' executing', [
				'importer' => $importerName,
				'script' => $scriptPath,
				'data_size' => strlen($jsonInput),
				'class' => static::class
			]);
			$descriptors = [
				0 => ['pipe', 'r'],
				1 => ['pipe', 'w'],
				2 => ['pipe', 'w'],
			];
			$pythonPath = self::_resolveImportPythonBinary();
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
					'importer' => $importerName,
					'command' => $command,
					'class' => static::class
				]);
				throw new RuntimeException(
					"Failed to start Python import process for {$importerName}"
				);
			}
			stream_set_blocking($pipes[0], false);
			stream_set_blocking($pipes[1], false);
			stream_set_blocking($pipes[2], false);
			$written = fwrite($pipes[0], $jsonInput);
			fclose($pipes[0]);
			if ($written === false || $written !== strlen($jsonInput)) {
				Log::warning(__METHOD__ . ' incomplete stdin write', [
					'importer' => $importerName,
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
				if ((time() - $startTime) > self::PYTHON_IMPORT_TIMEOUT) {
					Log::error(__METHOD__ . ' timeout', [
						'importer' => $importerName,
						'timeout' => self::PYTHON_IMPORT_TIMEOUT,
						'class' => static::class
					]);
					foreach ([1, 2] as $pipeIdx) {
						if (isset($pipes[$pipeIdx]) && is_resource($pipes[$pipeIdx])) fclose($pipes[$pipeIdx]);
					}
					proc_terminate($process, 9);
					proc_close($process);
					throw new RuntimeException(
						"Python import timeout for {$importerName}"
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
					'importer' => $importerName,
					'stderr' => substr($stderr, 0, 2000),
					'class' => static::class
				]);
			}
			if ($exitCode !== 0) {
				Log::error(__METHOD__ . ' non-zero exit', [
					'importer' => $importerName,
					'exit_code' => $exitCode,
					'stderr' => substr($stderr, 0, 1000),
					'class' => static::class
				]);
				throw new RuntimeException(
					"Python import failed with code {$exitCode}: " .
						substr($stderr, 0, 500)
				);
			}
			$decoded = json_decode($stdout, true, 512, JSON_THROW_ON_ERROR);
			$result = is_array($decoded) ? $decoded : [];
			Log::info(__METHOD__ . ' success', [
				'importer' => $importerName,
				'imported' => $result['imported'] ?? 0,
				'skipped' => $result['skipped'] ?? 0,
				'errors' => count($result['errors'] ?? []),
				'class' => static::class
			]);
		} catch (\JsonException $e) {
			Log::error(__METHOD__ . ' JSON error', [
				'importer' => $importerName,
				'error' => $e->getMessage(),
				'class' => static::class
			]);
			$result = ['status' => 'error', 'errors' => [$e->getMessage()], 'rows' => []];
		} catch (RuntimeException $e) {
			Log::error(__METHOD__ . ' runtime error', [
				'importer' => $importerName,
				'error' => $e->getMessage(),
				'class' => static::class
			]);
			$result = ['status' => 'error', 'errors' => [$e->getMessage()], 'rows' => []];
		} catch (\Throwable $e) {
			Log::error(__METHOD__ . ' unexpected error', [
				'importer' => $importerName,
				'error_class' => get_class($e),
				'error' => $e->getMessage(),
				'class' => static::class
			]);
			$result = ['status' => 'error', 'errors' => [$e->getMessage()], 'rows' => []];
		}
		return $result;
	}

	/**
	 * Resolve the full path to a Python import script.
	 */
	protected static function _resolveImportPythonScript(string $importerName): string
	{
		$scriptName ??= '';
		$scriptPath ??= '';
		try {
			$scriptName = self::_importerToScriptName($importerName);
			$scriptPath = realpath(self::PYTHON_IMPORT_SCRIPTS_PATH . $scriptName);
			if ($scriptPath === false) {
				$scriptPath = self::PYTHON_IMPORT_SCRIPTS_PATH . $scriptName;
			}
		} catch (\Throwable $e) {
			Log::error(__METHOD__ . ' resolution failed', [
				'importer' => $importerName,
				'error' => $e->getMessage()
			]);
			$scriptPath = '';
		}
		return is_string($scriptPath) ? $scriptPath : '';
	}

	/**
	 * Convert PascalCase importer name to snake_case Python script name.
	 */
	protected static function _importerToScriptName(string $importerName): string
	{
		$result ??= '';
		try {
			$snake = strtolower(preg_replace(
				'/([a-z])([A-Z])/',
				'$1_$2',
				str_replace('Import', '', $importerName)
			) ?? '');
			$result = $snake . '_importer.py';
		} catch (\Throwable $e) {
			Log::error(__METHOD__ . ' name conversion failed', [
				'importer' => $importerName,
				'error' => $e->getMessage()
			]);
			$result = '';
		}
		return $result;
	}

	/**
	 * Resolve the Python binary path from config/env.
	 */
	protected static function _resolveImportPythonBinary(): string
	{
		$binary ??= self::PYTHON_IMPORT_BINARY;
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
}
