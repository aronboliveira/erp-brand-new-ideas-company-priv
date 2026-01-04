<?php

namespace App\Traits;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, SupportsConstants as SC};
use App\Models\Utility;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{DB, Log, Schema};

trait FiltersSecureAttachments
{
	protected static function bootFiltersSecureAttachments(): void
	{
		static::saving(function (Model $m): void {
			try {
				$table = $m->getTable();
				if (!Schema::hasTable($table))
					return;
				$hasAttachment = Schema::hasColumn($table, 'attachment');
				$hasFile = Schema::hasColumn($table, 'file');
				$hasOther = Schema::hasColumn($table, SC::COL_OTHER_ATTACHMENTS);
				$hasAttachments = Schema::hasColumn($table, 'attachments');
				if (!$hasAttachment && !$hasOther && !$hasAttachments)
					return;
				if ($hasAttachment)
					$m->setAttribute('attachment', self::sanitizeAttachmentValue($m->getAttribute('attachment'), $m));
				if ($hasFile) {
					try {
						$columnType = Schema::getColumnType($table, 'file');
						if (in_array($columnType, ['string', 'text', 'uuid'], true))
							$m->setAttribute('file', self::sanitizeAttachmentValue($m->getAttribute('file'), $m));
					} catch (\Throwable $e) {
						Log::notice(static::class . ' failed getting column type for file attachment', [
							'file' => $e->getFile(),
							'line' => $e->getLine(),
							'error' => $e->getMessage(),
							'table' => $m->getTable(),
							'model_id' => $m->getKey(),
						]);
					}
				}
				foreach ([SC::COL_OTHER_ATTACHMENTS, 'attachments'] as $col) {
					if (Schema::hasColumn($table, $col)) {
						$raw = $m->getAttribute($col);
						$arr = self::normalizeAttachmentList($raw);
						if ($arr === null) {
							$m->setAttribute($col, null);
							continue;
						}
						$out = [];
						foreach ($arr as $v) {
							$sv = self::sanitizeAttachmentValue($v, $m);
							if ($sv !== null)
								$out[] = $sv;
						}
						$m->setAttribute($col, $out ?: null);
					}
				}
				if (Schema::hasColumn($table, 'url'))
					$m->setAttribute('url', self::validateSafeUrl($m->getAttribute('url')));
				foreach (array_values(array_unique([PJC::COL_IMG_PATH, DC::COL_FL_PT, PJC::COL_F_PATH])) as $col)
					if (Schema::hasColumn($table, $col))
						$m->setAttribute($col, self::validatePath($m->getAttribute($col)));
			} catch (\Throwable $e) {
				Log::error(static::class . ' secure attachment filter failed', [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'table' => $m->getTable(),
					'model_id' => $m->getKey(),
				]);
			}
		});
	}

	protected static function normalizeAttachmentList(mixed $value): ?array
	{
		if ($value === null)
			return null;

		if (is_string($value)) {
			$trim = trim($value);
			if ($trim === '')
				return null;
			try {
				$decoded = json_decode($trim, true, 512, JSON_THROW_ON_ERROR);
				$value = $decoded;
			} catch (\Throwable) {
				$value = [$trim];
			}
		}

		if (!is_array($value))
			$value = (array) $value;

		$out = [];
		foreach ($value as $v) {
			if (!is_scalar($v))
				continue;
			$s = trim((string) $v);
			if ($s === '')
				continue;
			$out[] = $s;
		}

		$out = array_values(array_unique($out));
		return $out ?: null;
	}

	protected static function sanitizeAttachmentValue(mixed $value, Model $m): ?string
	{
		$v = trim((string) $value);
		if ($v === '')
			return null;
		if (Utility::looksLikeUuid($v)) {
			try {
				if (DB::table(DC::TABLE_DOCS)->where('id', $v)->exists())
					return $v;
			} catch (\Throwable $e) {
				Log::error(static::class . ' failed checking Document attachment uuid', [
					'file' => $e->getFile(),
					'line' => $e->getLine(),
					'error' => $e->getMessage(),
					'doc_id' => $v,
					'table' => $m->getTable(),
					'model_id' => $m->getKey(),
				]);
			}
		}
		if (str_starts_with($v, 'https://'))
			return self::validateSafeUrl($v);
		return self::validatePath($v);
	}

	protected static function validateSafeUrl(mixed $v): ?string
	{
		$v = trim((string) $v);
		if ($v === '')
			return null;
		if (str_starts_with($v, 'https://')) {
			$host = parse_url($v, PHP_URL_HOST);
			$appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
			if (!$host) return null;
			if ($appHost && strtolower($host) === strtolower($appHost))
				return $v;
			$trustedDomains = [
				// Cloud Storage - User-focused
				'drive.google.com',
				'docs.google.com',
				'storage.googleapis.com',
				'onedrive.live.com',
				'1drv.ms',
				'sharepoint.com',
				'dropbox.com',
				'dl.dropboxusercontent.com',
				'box.com',
				'icloud.com',

				// Cloud Storage - Server/CDN
				's3.amazonaws.com',
				'amazonaws.com', // Covers all S3 regional endpoints
				'cloudfront.net',
				's3.wasabisys.com',
				'wasabisys.com',
				'digitaloceanspaces.com',
				'backblazeb2.com',
				'storage.cloud.google.com',
				'blob.core.windows.net', // Azure Blob Storage
				'azureedge.net', // Azure CDN
				'cloudflare.com',
				'cloudflarestream.com',
				'bunny.net', // BunnyCDN
				'fastly.net',

				// Enterprise/Productivity
				'notion.so',
				'notion.site',
				'atlassian.net', // Jira, Confluence
				'jira.com',
				'slack.com',
				'slack-files.com',
				'asana.com',
				'trello.com',
				'airtable.com',
				'monday.com',
				'figma.com',
				'miro.com',

				// Big Tech
				'microsoft.com',
				'office.com',
				'live.com',
				'google.com',
				'youtube.com',
				'youtu.be',
				'apple.com',
				'icloud.com',
				'meta.com',
				'facebook.com',
				'instagram.com',
			];
			$hostLower = strtolower($host);
			foreach ($trustedDomains as $trustedDomain) {
				$trustedLower = strtolower($trustedDomain);
				if ($hostLower === $trustedLower || str_ends_with($hostLower, '.' . $trustedLower))
					return $v;
			}
			return null;
		}
		return null;
	}

	protected static function validatePath(mixed $v): ?string
	{
		$v = trim((string) $v);
		if ($v === '')
			return null;
		if (str_starts_with($v, 'att://')) {
			$hash = substr($v, 6);
			if ($hash === '' || $hash === false)
				return null;
			if (preg_match('/^[a-f0-9]{20,}$/i', $hash))
				return $v;
			return null;
		}
		if (str_starts_with($v, 'file://') || str_starts_with($v, 'blob:'))
			return $v;
		if (str_starts_with($v, 'data:')) {
			if (preg_match('/^data:[a-z]+\/[a-z0-9\-\+\.]+;base64,/i', $v))
				return $v;
			return null;
		}
		try {
			if (file_exists($v))
				return $v;
			$p = storage_path('app/' . ltrim($v, '/'));
			return file_exists($p) ? $v : null;
		} catch (\Throwable $e) {
			Log::warning(static::class . ' failed checking attachment path', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
				'value' => $v,
			]);
			return null;
		}
	}
}
