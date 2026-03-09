<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Enums\MimeType;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DealEmailSeeder extends Seeder
{
	// Reduzindo drasticamente a chance de null (apenas 5%)
	private const OPTIONALITY = 0.05;

	/**
	 * CLI:
	 *  --min=N   Número mínimo de emails por deal (padrão: 2)
	 *  --max=N   Número máximo de emails por deal (padrão: 64)
	 */
	public function run(): void
	{
		$faker = fake();

		// ---------- Pré-checagens ----------
		$tables = [DC::TABLE_DL_EMAILS, DC::TABLE_DEALS, DC::TABLE_USERS];
		foreach ($tables as $table) {
			if (!Schema::hasTable($table)) {
				$this->command?->warn(sprintf('DealEmailSeeder: tabela %s ausente; abortando.', $table));
				return;
			}
		}

		// ---------- Contagem total fixa: 64 x N ----------
		$dealsCount = (int) DB::table(DC::TABLE_DEALS)->count();
		if ($dealsCount === 0) {
			$this->command?->warn('DealEmailSeeder: não há deals; nada a semear.');
			return;
		}

		$totalEmailsToCreate = 16 * $dealsCount;

		// ---------- Configuração de limites por deal ----------
		$minPerDeal = 2;
		$maxPerDeal = 16;

		if ($this->command instanceof Command) {
			if ($this->command->hasOption('min')) {
				try {
					$raw = $this->command->option('min');
					if (is_numeric($raw) && (int) $raw >= 1) {
						$minPerDeal = (int) $raw;
					}
				} catch (\Throwable) {
					// Usa valor padrão
				}
			}

			if ($this->command->hasOption('max')) {
				try {
					$raw = $this->command->option('max');
					if (is_numeric($raw) && (int) $raw >= $minPerDeal) {
						$maxPerDeal = (int) $raw;
					}
				} catch (\Throwable) {
					// Usa valor padrão
				}
			}
		}

		// ---------- Otimização de queries ----------
		$dealIds = DB::table(DC::TABLE_DEALS)->pluck('id')->all();
		$userIds = DB::table(DC::TABLE_USERS)->pluck('id')->all();

		if (empty($userIds)) {
			$this->command?->warn('DealEmailSeeder: nenhum usuário disponível; abortando.');
			return;
		}

		// ---------- Utilitários ----------
		$randBool = function (int $pct) use ($faker): bool {
			return $faker->boolean(max(0, min(100, $pct)));
		};

		$maybe = function (?callable $producer = null) use ($randBool) {
			return $randBool((int) round(self::OPTIONALITY * 100))
				? ($producer ? $producer() : null)
				: null;
		};

		// ---------- Preparação de dados do enum ----------
		$allMimeTypes = MimeType::cases();

		// Categorias de mime types para regras de filtro
		$documentMimes = array_values(array_filter($allMimeTypes, fn(MimeType $mime) => $mime->isDocument()));
		$imageMimes = array_values(array_filter($allMimeTypes, fn(MimeType $mime) => $mime->isImage()));
		$textMimes = array_values(array_filter($allMimeTypes, fn(MimeType $mime) => $mime->isText()));

		// ---------- Cache de emails únicos ----------
		$existingEmails = DB::table(DC::TABLE_DL_EMAILS)
			->select('from', 'to')
			->whereNotNull('from')
			->orWhereNotNull('to')
			->get()
			->flatMap(fn($row) => [
				strtolower(trim($row->from ?? '')),
				strtolower(trim($row->to ?? ''))
			])
			->filter()
			->unique()
			->values()
			->all();

		$generateUniqueEmail = function () use ($faker, &$existingEmails): string {
			do {
				$email = strtolower(trim($faker->unique()->safeEmail()));
			} while (in_array($email, $existingEmails, true));

			$existingEmails[] = $email;
			return $email;
		};

		// ---------- Cálculo de distribuição de emails por deal ----------
		$emailsPerDeal = [];
		$emailsRemaining = $totalEmailsToCreate;
		$dealsRemaining = count($dealIds);

		foreach ($dealIds as $index => $dealId) {
			if ($dealsRemaining === 1) {
				$emailsPerDeal[$dealId] = $emailsRemaining;
			} else {
				$avg = floor($emailsRemaining / $dealsRemaining);
				$min = max($minPerDeal, $avg - floor($avg * 0.3));
				$max = min($maxPerDeal, $avg + floor($avg * 0.3));
				$emailsForThisDeal = $faker->numberBetween($min, $max);
				$emailsForThisDeal = min($emailsForThisDeal, $emailsRemaining - ($dealsRemaining - 1) * $minPerDeal);
				$emailsForThisDeal = max($minPerDeal, $emailsForThisDeal);

				$emailsPerDeal[$dealId] = $emailsForThisDeal;
				$emailsRemaining -= $emailsForThisDeal;
				$dealsRemaining--;
			}
		}

		// ---------- Inserção ----------
		DB::transaction(function () use (
			$faker,
			$dealIds,
			$userIds,
			$emailsPerDeal,
			$totalEmailsToCreate,
			$maybe,
			$generateUniqueEmail,
			$allMimeTypes,
			$documentMimes,
			$imageMimes,
			$textMimes
		): void {
			$emailsToInsert = [];
			$processedCount = 0;
			$HARD_CAP = 2;
			$created = 0;

			foreach ($emailsPerDeal as $dealId => $emailCount) {
				if ($created >= $HARD_CAP) break;
				for ($j = 0; $j < $emailCount; $j++) {
					try {
						$now = Carbon::now()->subDays($faker->numberBetween(0, 180))
							->subMinutes($faker->numberBetween(0, 1440));

						// user_id: nullableUser: true, mas com baixa chance de null (5%)
						$userId = $maybe(fn() => $userIds[array_rand($userIds)]) ?? $userIds[array_rand($userIds)];

						// from_id/to_id: baixa chance de null (5%)
						$fromUserId = $maybe(fn() => $userIds[array_rand($userIds)]) ?? $userIds[array_rand($userIds)];
						$toUserId = $maybe(fn() => $userIds[array_rand($userIds)]) ?? $userIds[array_rand($userIds)];

						// DealEmail SÓ USA EMAILS, não telefones
						$fromAddr = $generateUniqueEmail();
						$toAddr = $generateUniqueEmail();

						// Subject (sempre preenchido)
						$subject = $faker->boolean(30)
							? ('Re: ' . $faker->sentence(5))
							: $faker->sentence(6);

						// Description e notes: baixa chance de null (5%)
						$description = $maybe(fn() => $faker->paragraphs($faker->numberBetween(1, 3), true))
							?? $faker->paragraphs($faker->numberBetween(1, 3), true);
						$notes = $maybe(fn() => $faker->sentences($faker->numberBetween(1, 2), true))
							?? $faker->sentences($faker->numberBetween(1, 2), true);

						// Counter: baixa chance de null (5%)
						$counter = $faker->numberBetween(1, 12);
						$counterValue = $maybe() ? null : $counter;

						// Follow-up: baixa chance de null (5%)
						$isFollowUp = $faker->boolean(45);
						$isFollowUpValue = $maybe() ? null : $isFollowUp;

						// Attachments: baixa chance de null (5%)
						$attachmentsData = $maybe(function () use ($faker, $allMimeTypes) {
							$n = $faker->numberBetween(1, 4);
							$items = [];
							for ($k = 0; $k < $n; $k++) {
								$mimeType = $allMimeTypes[array_rand($allMimeTypes)];
								$ext = $mimeType->getExtension();
								$name = $faker->slug() . '.' . $ext;

								$items[] = [
									'name'     => $name,
									'mimetype' => $mimeType->value,
									'size_kb'  => $faker->numberBetween(3, 8_192),
									'url'      => $faker->url(),
									'sha256'   => hash('sha256', $faker->unique()->uuid()),
								];
							}
							return $items;
						});

						if (!$attachmentsData) {
							// Mesmo quando não é null, pode ser array vazio
							$attachmentsData = $faker->boolean(30) ? [] : null;
						}

						$attachmentsJson = $attachmentsData !== null
							? json_encode($attachmentsData, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
							: null;

						// Attachment filter rules: baixa chance de null (5%)
						$rulesData = $maybe(function () use (
							$faker,
							$documentMimes,
							$imageMimes,
							$textMimes
						) {
							// Categorias comuns para emails
							$categories = [
								['mimes' => $documentMimes, 'weight' => 60],
								['mimes' => $imageMimes, 'weight' => 30],
								['mimes' => $textMimes, 'weight' => 10],
							];

							$filteredCategories = array_values(array_filter(
								$categories,
								fn($category) => !empty($category['mimes'])
							));

							if (empty($filteredCategories)) {
								$filteredCategories = [['mimes' => $documentMimes]];
							}

							$selectedCategory = $faker->randomElement($filteredCategories);

							$allowedMimes = $faker->randomElements(
								array_column($selectedCategory['mimes'], 'value'),
								$faker->numberBetween(1, min(4, count($selectedCategory['mimes'])))
							);

							$denyExt = $faker->boolean(25)
								? $faker->randomElements(['exe', 'bat', 'js', 'vbs', 'cmd', 'ps1', 'scr'], $faker->numberBetween(1, 3))
								: [];

							return [
								'allowed_mime'   => array_values($allowedMimes),
								'blocked_ext'    => array_values($denyExt),
								'max_size_kb'    => $faker->randomElement([2048, 4096, 8192]),
								'name_regex'     => $faker->boolean(20) ? '^[a-z0-9_\-\.]+$' : null,
								'hash_required'  => $faker->boolean(30),
							];
						});

						$rulesJson = $rulesData !== null
							? json_encode($rulesData, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
							: null;

						// Auditoria: criador/atualizador com baixa chance de null (5%)
						$creatorId = $maybe(fn() => $userIds[array_rand($userIds)]) ?? $userIds[array_rand($userIds)];
						$updaterId = $maybe(fn() => $userIds[array_rand($userIds)]) ?? $userIds[array_rand($userIds)];

						// Construir array de inserção conforme $fillable do model
						$emailData = [
							'id'                     => (string) Str::uuid(),
							AC::COL_DL              => $dealId,
							UC::COL_USER_ID         => $userId,
							'from'                  => $fromAddr,
							'to'                    => $toAddr,
							AC::COL_FRM_ID          => $fromUserId,
							AC::COL_TO_ID           => $toUserId,
							'subject'               => mb_substr($subject, 0, 255),
							'description'           => $description,
							'notes'                 => $notes,
							'counter'               => $counterValue,
							PJC::COL_IS_FUP         => $isFollowUpValue,
							'attachments'           => $attachmentsJson,
							PJC::COL_ATC_FRULES     => $rulesJson,
							DC::COL_TABLE_CREATOR   => $creatorId,
							// updated_by não está no $fillable do model, mas a migration tem
							DC::COL_TABLE_UPDATER   => $updaterId,
							'created_at'            => $now->toDateTimeString(),
							'updated_at'            => $now->addMinutes($faker->numberBetween(1, 720))->toDateTimeString(),
						];

						$emailsToInsert[] = $emailData;
						$processedCount++;
						$created++;
						// (new \Symfony\Component\Console\Output\ConsoleOutput)->writeln("Criando E-mail sobre Acordo de Negócios {$dealId} de {$fromAddr} para {$toAddr} sobre o assunto '{$subject}'");
						// Insere em lotes para melhor performance
						if (count($emailsToInsert) >= 500) {
							DB::table(DC::TABLE_DL_EMAILS)->insert($emailsToInsert);
							$emailsToInsert = [];
						}
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}

			// Insere os emails restantes
			if (!empty($emailsToInsert)) {
				DB::table(DC::TABLE_DL_EMAILS)->insert($emailsToInsert);
			}

			$this->command?->info(sprintf(
				'DealEmailSeeder: %d emails criados para %d deals (média de %.1f por deal).',
				$totalEmailsToCreate,
				count($dealIds),
				$totalEmailsToCreate / count($dealIds)
			));
		});
	}
}
