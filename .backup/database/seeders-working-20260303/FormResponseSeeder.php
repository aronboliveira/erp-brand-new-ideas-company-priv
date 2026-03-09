<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, FormsConstants as FC, ProjectsConstants as PJC};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class FormResponseSeeder extends Seeder
{
	private const HARD_CAP = 64000;
	private $out = null;
	public function run(): void
	{
		$this->out = new \Symfony\Component\Console\Output\ConsoleOutput();
		$formTable = DC::TABLE_FORM_BUILD;
		$responseTable = DC::TABLE_FORM_RSP;
		$fieldRspTable = DC::TABLE_FM_FLD_RSP;

		if (!DB::getSchemaBuilder()->hasTable($formTable)) {
			$this->out->writeln('<comment>FormResponseSeeder: form table does not exist, skipping seeding.</comment>');
			return;
		}
		if (!DB::getSchemaBuilder()->hasTable($responseTable)) {
			$this->out->writeln('<comment>FormResponseSeeder: form response table does not exist, skipping seeding.</comment>');
			return;
		}
		if (!DB::getSchemaBuilder()->hasTable($fieldRspTable)) {
			$this->out->writeln('<comment>FormResponseSeeder: form field response table does not exist, skipping seeding.</comment>');
			return;
		}

		$formIds = DB::table($formTable)->pluck('id')->all();
		if (!$formIds) {
			$this->out->writeln('<comment>FormResponseSeeder: no forms found, skipping seeding.</comment>');
			return;
		}

		$faker = fake();
		$created = 0;

		foreach ($formIds as $formId) {
			if ($created >= self::HARD_CAP) {
				$this->out->writeln('<comment>FormResponseSeeder: reached hard cap of ' . self::HARD_CAP . ' records, stopping seeding.</comment>');
				break;
			}

			$iterations = random_int(0, 64);
			if ($iterations <= 0)
				continue;

			for ($i = 0; $i < $iterations; $i++) {
				if ($created >= self::HARD_CAP) {
					$this->out->writeln('<comment>FormResponseSeeder: reached hard cap of ' . self::HARD_CAP . ' records, stopping seeding.</comment>');
					break 2;
				}

				$candidates = DB::table($fieldRspTable)
					->where(FC::COL_FM_ID, $formId)
					->inRandomOrder()
					->limit(64)
					->get(['id', 'type', 'name', FC::COL_HTML_ID, 'value', 'checked', 'metadata', 'created_at']);

				if ($candidates->isEmpty()) {
					$this->out->writeln('<comment>FormResponseSeeder: no available field responses for form ' . $formId . ', skipping.</comment>');
					break;
				}

				$take = random_int(1, min(64, $candidates->count()));
				$selected = $candidates->take($take)->values();

				$submittedBy = null;
				foreach ($selected as $row) {
					if (!empty($row->{PJC::COL_SBM_BY} ?? null)) {
						$submittedBy = $row->{PJC::COL_SBM_BY};
						break;
					}
				}

				$submittedAt = now()->subDays(random_int(0, 180))->subMinutes(random_int(0, 24 * 60));
				$submittedEmail = null;

				if ($submittedBy !== null && DB::getSchemaBuilder()->hasTable(DC::TABLE_USERS)) {
					try {
						$submittedEmail = DB::table(DC::TABLE_USERS)->where('id', $submittedBy)->value('email');
					} catch (\Throwable $e) {
						Log::debug(static::class . ' failed to fetch user email', [
							'user_id' => $submittedBy,
							'error' => $e->getMessage(),
							'file' => $e->getFile(),
							'line' => $e->getLine(),
						]);
					}
				}

				$payload = [];
				foreach ($selected as $row) {
					$key = trim((string) ($row->name ?? ''));
					if ($key === '') $key = trim((string) ($row->{FC::COL_HTML_ID} ?? ''));
					if ($key === '') $key = 'field_' . Str::lower(Str::random(10));

					$isCheckable = in_array((string) ($row->type ?? ''), ['checkbox', 'radiogroup'], true);

					if ($isCheckable) $payload[$key] = (bool) ($row->checked ?? false);
					else $payload[$key] = $row->value;
				}

				$status = $faker->randomElement(['pending', 'approved', 'rejected', 'archived']);

				$captchaOk = (bool) random_int(0, 1);
				$consentOk = (bool) random_int(0, 1);
				$csrfOk = (bool) random_int(0, 1);
				$mwFree = true;

				$expAt = now()->addDays(730)->subDays(random_int(0, 120));

				$responseId = (string) Str::uuid();

				DB::beginTransaction();
				try {
					DB::table($responseTable)->insert([
						'id' => $responseId,
						FC::COL_FM_ID => $formId,
						PJC::COL_SBM_AT => $submittedAt,
						PJC::COL_SBM_BY => $submittedBy,
						FC::COL_SBM_EML => $submittedEmail ? strtolower(trim((string) $submittedEmail)) : null,
						FC::COL_SBM_IP => $faker->ipv4(),
						FC::COL_SBM_UA => $faker->userAgent(),
						FC::COL_SBM_URL => $faker->url(),
						'response' => json_encode($payload, JSON_UNESCAPED_UNICODE),
						'status' => $status,
						FC::COL_CAPTCHA_APV => $captchaOk,
						FC::COL_CST_CHK => $consentOk,
						FC::COl_CSRF_TKN_APV => $csrfOk,
						DC::COL_MW_FREE => $mwFree,
						FC::COL_EXP_AT => $expAt,
						'lakes' => json_encode([], JSON_UNESCAPED_UNICODE),
						'edits' => json_encode([], JSON_UNESCAPED_UNICODE),
						'metadata' => json_encode([
							'seed' => true,
							'seed_context' => 'FormResponsesSeeder',
							'fields_used' => $selected->pluck('id')->all(),
						], JSON_UNESCAPED_UNICODE),
					]);

					$idsToUpdate = $selected->pluck('id')->all();
					$this->out->writeln('<info>[FormResponseSeeder]</info> Linking ' . count($idsToUpdate) . ' field responses to form response ' . $responseId . ' for form ' . $formId . '.');
					DB::table($fieldRspTable)
						->whereIn('id', $idsToUpdate)
						->update([
							FC::COL_FM_DT_RSP_ID => $responseId,
						]);

					DB::commit();
					$created++;
				} catch (\Throwable $e) {
					DB::rollBack();
					Log::warning(static::class . ' failed creating response + linking field responses', [
						'form_id' => $formId,
						'response_id' => $responseId,
						'error' => $e->getMessage(),
						'file' => $e->getFile(),
						'line' => $e->getLine(),
					]);
				}
			}
		}
	}
}
