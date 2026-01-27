<?php

namespace App\Services;

use App\Config\Constants\{DatabaseConstants as DC, TemplatesConstants as TC};
use App\Models\{ExperienceCertificate, JoiningLetter, Noc};
use Illuminate\Support\Facades\{Auth, Log};

class TemplateRequestService
{
	public function ensureDefaultExpCertificate(ExperienceCertificate $model, ?string $userId = null): void
	{
		$creatorId = $userId ?? Auth::id();
		if (!$creatorId) {
			Log::warning(static::class . '::' . __FUNCTION__ . ' skipped: no user id resolved', [
				'model' => $model::class,
			]);
			return;
		}
		foreach ($model::DEFAULT_XP_CERTIFICATE as $lang => $content) {
			try {
				$model::firstOrCreate(
					[
						TC::COL_LG => $lang,
						DC::COL_TABLE_CREATOR => (string) $creatorId,
					],
					[
						TC::COL_CT => $content,
					]
				);
			} catch (\Throwable $e) {
				Log::error(static::class . '::' . __FUNCTION__ . " failed for lang [{$lang}]: {$e->getMessage()}", [
					'model' => $model::class,
					'creator_id' => (string) $creatorId,
				]);
			}
		}
	}

	public function ensureDefaultJoiningLetter(JoiningLetter $model, ?string $userId = null): void
	{
		$creatorId = $userId ?? Auth::id();

		if (!$creatorId) {
			Log::warning(static::class . '::' . __FUNCTION__ . ' skipped: no user id resolved', [
				'model' => $model::class,
			]);
			return;
		}

		foreach ($model::DEFAULT_JOINING_LETTER as $lang => $content) {
			try {
				$model::firstOrCreate(
					[
						TC::COL_LG => $lang,
						DC::COL_TABLE_CREATOR => (string) $creatorId,
					],
					[
						TC::COL_CT => $content,
					]
				);
			} catch (\Throwable $e) {
				Log::error(static::class . '::' . __FUNCTION__ . " failed for lang [{$lang}]: {$e->getMessage()}", [
					'model' => $model::class,
					'creator_id' => (string) $creatorId,
				]);
			}
		}
	}

	public function ensureDefaultNocCertificate(Noc $model, ?string $userId = null): void
	{
		$creatorId = $userId ?? Auth::id();

		if (!$creatorId) {
			Log::warning(static::class . '::' . __FUNCTION__ . ' skipped: no user id resolved', [
				'model' => $model::class,
			]);
			return;
		}

		foreach ($model::DEFAULT_NOC_CERTIFICATE as $lang => $content) {
			try {
				$model::firstOrCreate(
					[
						TC::COL_LG => $lang,
						DC::COL_TABLE_CREATOR => (string) $creatorId,
					],
					[
						TC::COL_CT => $content,
					]
				);
			} catch (\Throwable $e) {
				Log::error(static::class . '::' . __FUNCTION__ . " failed for lang [{$lang}]: {$e->getMessage()}", [
					'model' => $model::class,
					'creator_id' => (string) $creatorId,
				]);
			}
		}
	}
}
