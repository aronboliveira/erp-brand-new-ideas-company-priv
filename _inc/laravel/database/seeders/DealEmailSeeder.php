<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DealEmailSeeder extends Seeder
{
	// Probabilidade base (0–1) de um campo opcional virar null (tolerância controlada)
	private const OPTIONALITY = 0.38;

	/**
	 * CLI:
	 *  --count=N  Quantidade de registros a gerar (se ausente, usa 64 * nº de deals)
	 */
	public function run(): void
	{
		$faker = fake();

		// ---------- Pré-checagens ----------
		if (!Schema::hasTable(DC::TABLE_DL_EMAILS)) {
			$this->command?->warn('DealEmailSeeder: tabela de emails de negócio ausente; abortando.');
			return;
		}
		if (!Schema::hasTable(DC::TABLE_DEALS)) {
			$this->command?->warn('DealEmailSeeder: tabela de deals ausente; nada a fazer.');
			return;
		}
		if (!Schema::hasTable(DC::TABLE_USERS)) {
			$this->command?->warn('DealEmailSeeder: tabela de users ausente; nada a fazer.');
			return;
		}

		$dealsCount = (int) DB::table(DC::TABLE_DEALS)->count();
		if ($dealsCount === 0) {
			$this->command?->warn('DealEmailSeeder: não há deals; nada a semear.');
			return;
		}

		// ---------- Regras de quantidade (mock rule do projeto) ----------
		$defaultCount = max(64, 64 * $dealsCount);
		$count        = $defaultCount;

		if ($this->command instanceof Command && $this->command->hasOption('count')) {
			try {
				$raw = $this->command->option('count');
				if (is_numeric($raw) && (int) $raw > 0) {
					$count = (int) $raw;
				} else {
					$this->command?->warn(sprintf(
						'DealEmailSeeder: valor inválido para --count (%s); usando %d.',
						(string) $raw,
						$defaultCount
					));
				}
			} catch (\Throwable) {
				$this->command?->warn('DealEmailSeeder: falha ao ler --count; usando valor padrão.');
				$count = $defaultCount;
			}
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
		$pickId = function (string $table): ?string {
			try {
				return DB::table($table)->inRandomOrder()->value('id');
			} catch (\Throwable) {
				return null;
			}
		};
		$endpoint = function () use ($faker): string {
			// 70% e-mail / 30% telefone E.164
			return $faker->boolean(70)
				? $faker->unique()->safeEmail()
				: $faker->e164PhoneNumber();
		};

		// ---------- Inserção ----------
		DB::transaction(function () use (
			$faker,
			$count,
			$randBool,
			$maybe,
			$pickId,
			$endpoint
		): void {
			// Pré-carrega IDs para minimizar overhead
			$dealIds  = DB::table(DC::TABLE_DEALS)->pluck('id')->all();
			$userIds  = DB::table(DC::TABLE_USERS)->pluck('id')->all();

			// Guardas
			if (empty($dealIds) || empty($userIds)) {
				// deal_id é NOT NULL; user_id pode ser NOT NULL conforme trait chamada
				// Se não houver usuários a referenciar, aborta para evitar violação de FK.
				return;
			}

			for ($i = 0; $i < $count; $i++) {
				$now   = Carbon::now()->subDays($faker->numberBetween(0, 180))
					->subMinutes($faker->numberBetween(0, 1440));
				$dealId = $dealIds[array_rand($dealIds)];

				// user_id: como a migration foi chamada com nullableUser: true (e a trait cria FK),
				// manteremos preenchido por segurança de FK (e coerência com autorização/owner).
				$userId = $userIds[array_rand($userIds)];

				// from_id/to_id opcionais (FK com nullOnDelete)
				$fromUserId = $maybe(fn() => $userIds[array_rand($userIds)]);
				$toUserId   = $maybe(fn() => $userIds[array_rand($userIds)]);

				// Endpoints textuais normalizados (email/telefone)
				$fromAddr = $endpoint();
				$toAddr   = $endpoint();

				// Subject e corpo
				$subject = $faker->boolean(30)
					? ('Re: ' . $faker->sentence(5))
					: $faker->sentence(6);

				$description = $maybe(fn() => $faker->paragraphs($faker->numberBetween(1, 3), true));
				$notes       = $maybe(fn() => $faker->sentences($faker->numberBetween(1, 2), true));

				// Mensagens trocadas no fio
				$counter = $faker->numberBetween(1, 12);

				// Follow-up?
				$isFollowUp = $faker->boolean(45);

				// Attachments (JSON)
				$attachmentsArr = $maybe(function () use ($faker) {
					$n = $faker->numberBetween(0, 4);
					$items = [];
					for ($k = 0; $k < $n; $k++) {
						$ext  = $faker->randomElement(['pdf', 'docx', 'xlsx', 'png', 'jpg', 'txt']);
						$name = $faker->slug() . '.' . $ext;
						$items[] = [
							'name'     => $name,
							'mimetype' => match ($ext) {
								'pdf'  => 'application/pdf',
								'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
								'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
								'png'  => 'image/png',
								'jpg'  => 'image/jpeg',
								default => 'text/plain',
							},
							'size_kb'  => $faker->numberBetween(3, 8_192),
							'url'      => $faker->url(),
							'sha256'   => hash('sha256', $faker->unique()->uuid()),
						];
					}
					return $items;
				});

				// Attachment filter rules (JSON)
				$rulesArr = $maybe(function () use ($faker) {
					$allow = $faker->randomElements(
						[
							'application/pdf',
							'image/png',
							'image/jpeg',
							'text/plain',
							'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
							'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
						],
						$faker->numberBetween(1, 4)
					);
					$denyExt = $faker->boolean(35)
						? $faker->randomElements(['exe', 'bat', 'js', 'vbs', 'cmd', 'ps1', 'scr'], $faker->numberBetween(1, 3))
						: [];
					return [
						'allowed_mime'   => array_values($allow),
						'blocked_ext'    => array_values($denyExt),
						'max_size_kb'    => $faker->randomElement([2048, 4096, 8192, 16384]),
						'name_regex'     => $faker->boolean(30) ? '^[a-z0-9_\-\.]+$' : null,
						'hash_required'  => $faker->boolean(40),
					];
				});

				// Auditoria (criador/atualizador podem ser nulos; manter diversidade)
				$creatorId = $maybe(fn() => $userIds[array_rand($userIds)]);
				$updaterId = $maybe(fn() => $userIds[array_rand($userIds)]);

				DB::table(DC::TABLE_DL_EMAILS)->insert([
					'id'                     => (string) Str::uuid(),

					// DealConnected
					AC::COL_DL              => $dealId,

					// IsBusinessContact (básico)
					UC::COL_USER_ID         => $userId,
					'from'                  => $fromAddr,
					AC::COL_TO_ID           => $toUserId,
					'to'                    => $toAddr,
					AC::COL_FRM_ID          => $fromUserId,
					'subject'               => mb_substr($subject, 0, 255),
					'description'           => $description,
					'notes'                 => $notes,

					// Email específico
					'counter'               => $maybe() === null ? $counter : null, // tolerância a null
					PJC::COL_IS_FUP         => $maybe() === null ? $isFollowUp : null,
					'attachments'           => isset($attachmentsArr) ? json_encode($attachmentsArr, JSON_UNESCAPED_UNICODE) : null,
					PJC::COL_ATC_FRULES     => isset($rulesArr) ? json_encode($rulesArr, JSON_UNESCAPED_UNICODE) : null,

					// Auditoria
					DC::COL_TABLE_CREATOR   => $creatorId,
					DC::COL_TABLE_UPDATER   => $updaterId,
					'created_at'            => $now->toDateTimeString(),
					'updated_at'            => $now->addMinutes($faker->numberBetween(1, 720))->toDateTimeString(),
				]);
			}
		});
	}
}
