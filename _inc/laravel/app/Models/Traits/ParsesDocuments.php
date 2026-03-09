<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC};
use Illuminate\Support\{Str};
use Illuminate\Support\Facades\{DB, Http, Log};

trait ParsesDocuments
{
		protected function allowedDocumentBaseDirs(): array
	{
		    try {
    		return array_values(array_filter([
    			realpath(storage_path('app')) ?: null,
    			realpath(public_path('storage')) ?: null,
    			realpath(public_path()) ?: null,
    		]));
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::allowedDocumentBaseDirs — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		        return [];
		    }
	}

		protected function appRootHost(): ?string
	{
		    try {
    		$appUrl = (string) env('APP_URL', '');
    		$appUrl = trim($appUrl);
    		if ($appUrl === '') return null;
    		$host = parse_url($appUrl, PHP_URL_HOST);
    		if (!is_string($host) || $host === '') return null;
    		$host = strtolower($host);
    		$parts = array_values(array_filter(explode('.', $host)));
    		if (count($parts) < 2) return $host;
    		return $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::appRootHost — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		        return '';
		    }
	}

		protected function isTrustedDocumentUrl(string $url): bool
	{
		    try {
    		$url = trim($url);
    		if ($url === '') return false;
    		$host = parse_url($url, PHP_URL_HOST);
    		if (!is_string($host) || $host === '') return false;
    		$host = strtolower($host);
    		$appUrl = (string) env('APP_URL', '');
    		$appHost = parse_url($appUrl, PHP_URL_HOST);
    		$appHost = is_string($appHost) ? strtolower($appHost) : null;
    		if ($appHost && $host === $appHost) return true;
    		$root = $this->appRootHost();
    		if (!$root) return false;
    		return $host === $root || Str::endsWith($host, '.' . $root);
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::isTrustedDocumentUrl — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		        return false;
		    }
	}

		protected function fetchDocumentMetaRaw(string $docId): ?array
	{
		    try {
    		$docId = trim($docId);
    		if ($docId === '') return null;
    		try {
    			$row = DB::table(DC::TABLE_DOCS)
    				->select(['id', DC::COL_FL_PT, 'url', 'mime', 'name'])
    				->where('id', $docId)
    				->first();
    			if (!$row) return null;
    			$fp = property_exists($row, DC::COL_FL_PT) ? $row->{DC::COL_FL_PT} : null;
    			$url = property_exists($row, 'url') ? $row->url : null;
    			$mime = property_exists($row, 'mime') ? $row->mime : null;
    			$name = property_exists($row, 'name') ? $row->name : null;
    			return [
    				'id' => (string) $row->id,
    				'file_path' => is_scalar($fp) ? (string) $fp : null,
    				'url' => is_scalar($url) ? (string) $url : null,
    				'mime' => is_scalar($mime) ? (string) $mime : null,
    				'name' => is_scalar($name) ? (string) $name : null,
    			];
    		} catch (\Throwable $t) {
    			Log::warning("[" . self::class . "]: " . static::class . ' failed to fetch doc meta', [
    				'doc_id' => $docId,
    				'error' => $t->getMessage(),
    				'method' => __METHOD__,
    				'file' => $t->getFile(),
    				'line' => $t->getLine(),
    			]);
    			return null;
    		}
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::fetchDocumentMetaRaw — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		        return [];
		    }
	}

	protected function sanitizeAndResolvePath(string $path): ?string
	{
	    try {
    		$path = trim($path);
    		if ($path === '') return null;
    		if (preg_match('/^[a-zA-Z]+:\/\//', $path)) return null;
    		$path = str_replace(["\0"], '', $path);
    		$real = realpath($path);
    		if (!$real) {
    			$candidate = storage_path('app/' . ltrim($path, '/'));
    			$real = realpath($candidate);
    		}
    		if (!$real) return null;
    		$bases = $this->allowedDocumentBaseDirs();
    		foreach ($bases as $base)
    			if (Str::startsWith($real, $base . DIRECTORY_SEPARATOR) || $real === $base)
    				return $real;
    		return null;
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::sanitizeAndResolvePath — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return '';
	    }
	}

	protected function readTextFromFilePath(string $filePath, int $maxBytes = 200000): ?string
	{
	    try {
    		$resolved = $this->sanitizeAndResolvePath($filePath);
    		if (!$resolved) return null;

    		try {
    			if (!is_file($resolved) || !is_readable($resolved)) return null;
    			$size = @filesize($resolved);
    			if (is_int($size) && $size > $maxBytes) {
    				$fh = @fopen($resolved, 'rb');
    				if (!$fh) return null;
    				$bin = @fread($fh, $maxBytes);
    				@fclose($fh);
    				if (!is_string($bin) || $bin === '') return null;
    				return $this->coerceToText($bin);
    			}
    			$bin = @file_get_contents($resolved);
    			if (!is_string($bin) || $bin === '') return null;
    			return $this->coerceToText($bin);
    		} catch (\Throwable $t) {
    			Log::warning("[" . self::class . "]: " . static::class . ' failed to read file path', [
    				'file_path' => $filePath,
    				'resolved' => $resolved,
    				'error' => $t->getMessage(),
    				'file' => $t->getFile(),
    				'line' => $t->getLine(),
    				'method' => __METHOD__
    			]);
    			return null;
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::readTextFromFilePath — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return '';
	    }
	}

	protected function fetchTextFromTrustedUrl(string $url, int $timeoutSeconds = 4, int $maxBytes = 200000): ?string
	{
	    try {
    		$url = trim($url);
    		if ($url === '' || !$this->isTrustedDocumentUrl($url)) return null;
    		try {
    			$head = Http::timeout($timeoutSeconds)->withoutRedirecting()->head($url);
    			if ($head->failed()) return null;
    			$ct = $head->header('Content-Type');
    			if (is_string($ct) && stripos($ct, 'text/') === false && stripos($ct, 'application/json') === false)
    				return null;
    			$res = Http::timeout($timeoutSeconds)->withoutRedirecting()->get($url);
    			if ($res->failed()) return null;
    			$body = (string) $res->body();
    			if ($body === '') return null;
    			if (strlen($body) > $maxBytes)
    				$body = substr($body, 0, $maxBytes);
    			return $this->coerceToText($body);
    		} catch (\Throwable $t) {
    			Log::warning("[" . self::class . "]: " . static::class . ' failed to fetch trusted url', [
    				'url' => $url,
    				'error' => $t->getMessage(),
    				'method' => __METHOD__,
    				'file' => $t->getFile(),
    				'line' => $t->getLine()
    			]);
    			return null;
    		}
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::fetchTextFromTrustedUrl — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return '';
	    }
	}

	protected function coerceToText(string $raw): ?string
	{
	    try {
    		$s = trim($raw);
    		if ($s === '') return null;
    		if (Str::contains($s, ['<html', '<body', '<p', '<div', '<br'], true)) {
    			$s = strip_tags($s);
    		}
    		$s = preg_replace("/[ \t]+/", ' ', $s);
    		$s = preg_replace("/\n{3,}/", "\n\n", (string) $s);
    		$s = trim((string) $s);
    		return $s !== '' ? $s : null;
	    } catch (\Throwable $e) {
	        Log::error(static::class . '::coerceToText — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
	        return '';
	    }
	}

		protected function resolveTextFromDocumentId(string $docId): ?string
	{
		    try {
    		$meta = $this->fetchDocumentMetaRaw($docId);
    		if (!$meta) return null;
    		$attempts = 0;
    		$maxAttempts = 3;
    		while ($attempts < $maxAttempts) {
    			$attempts++;
    			if (($meta['file_path'] ?? null) !== null) {
    				$t = $this->readTextFromFilePath((string) $meta['file_path']);
    				if ($t !== null) return $t;
    			}
    			if (($meta['url'] ?? null) !== null) {
    				$t = $this->fetchTextFromTrustedUrl((string) $meta['url']);
    				if ($t !== null) return $t;
    			}
    			break;
    		}
    		return null;
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::resolveTextFromDocumentId — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		        return '';
		    }
	}

		protected function normalizeDocumentRefItem(mixed $item): ?array
	{
		    try {
    		if ($item === null) return null;

    				if (is_array($item)) {
    			$id = isset($item['id']) && is_scalar($item['id']) ? trim((string) $item['id']) : null;
    			$url = isset($item['url']) && is_scalar($item['url']) ? trim((string) $item['url']) : null;
    			$fp  = isset($item['file_path']) && is_scalar($item['file_path']) ? trim((string) $item['file_path']) : null;
    			$nm  = isset($item['name']) && is_scalar($item['name']) ? trim((string) $item['name']) : null;

    			return [
    				'id' => $id !== '' ? $id : null,
    				'url' => ($url !== '' && $this->isTrustedDocumentUrl($url)) ? $url : null,
    				'file_path' => $fp !== '' ? $fp : null,
    				'name' => $nm !== '' ? $nm : null,
    			];
    		}

    		if (is_object($item)) {
    			$id = property_exists($item, 'id') && is_scalar($item->id) ? trim((string) $item->id) : null;
    			$url = property_exists($item, 'url') && is_scalar($item->url) ? trim((string) $item->url) : null;
    			$fp = property_exists($item, 'file_path') && is_scalar($item->file_path) ? trim((string) $item->file_path) : null;
    			$nm = property_exists($item, 'name') && is_scalar($item->name) ? trim((string) $item->name) : null;
    			return [
    				'id' => $id !== '' ? $id : null,
    				'url' => ($url !== '' && $this->isTrustedDocumentUrl($url)) ? $url : null,
    				'file_path' => $fp !== '' ? $fp : null,
    				'name' => $nm !== '' ? $nm : null,
    			];
    		}
    		if (is_scalar($item)) {
    			$s = trim((string) $item);
    			if ($s === '') return null;
    			if (Str::isUuid($s))
    				return ['id' => $s, 'url' => null, 'file_path' => null, 'name' => null];
    			if (preg_match('/^https?:\/\//i', $s))
    				return ['id' => null, 'url' => $this->isTrustedDocumentUrl($s) ? $s : null, 'file_path' => null, 'name' => null];
    			return ['id' => null, 'url' => null, 'file_path' => $s, 'name' => null];
    		}
    		return null;
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::normalizeDocumentRefItem — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		        return [];
		    }
	}

		protected function normalizeDocumentRefList(mixed $raw): ?array
	{
		    try {
    		if ($raw === null) return null;
    		if (!is_array($raw)) return null;
    		$out = [];
    		foreach ($raw as $item) {
    			$norm = $this->normalizeDocumentRefItem($item);
    			if ($norm === null)
    				continue;
    			$out[] = $norm;
    		}
    		return $out ?: null;
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::normalizeDocumentRefList — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		        return [];
		    }
	}

		protected function rescueTextFromLinkedDocumentIfEmpty(string $textColumn, string $docIdColumn): void
	{
		    try {
    		$cur = $this->getAttribute($textColumn);
    		$hasText = is_scalar($cur) && trim((string) $cur) !== '';
    		if ($hasText) return;
    		$docId = $this->getAttribute($docIdColumn);
    		if (!is_scalar($docId) || trim((string) $docId) === '') return;
    		$txt = $this->resolveTextFromDocumentId((string) $docId);
    		if ($txt !== null) {
    			$this->setAttribute($textColumn, $txt);
    		}
		    } catch (\Throwable $e) {
		        Log::error(static::class . '::rescueTextFromLinkedDocumentIfEmpty — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
		    }
	}
}
