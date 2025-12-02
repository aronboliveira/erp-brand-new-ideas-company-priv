<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\UserType;
use App\Models\LeadDiscussion;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Console\Command as ArtisanCommand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LeadDiscussionSeeder extends Seeder
{
	// Parâmetros fixos para mocking (não usar env())
	private const BATCH_SIZE       = 1000;
	private const DEF_MIN_ATTACHES = 0;
	private const DEF_MAX_ATTACHES = 3;
	private const DEF_MAX_REACTS   = 4;

	public function run(): void
	{
		// Sanidade de tabelas essenciais
		foreach ([DC::TABLE_LD_DSC, DC::TABLE_LEADS] as $tbl) {
			if (!Schema::hasTable($tbl)) {
				$this->command?->warn("Seeder abortado: tabela ausente {$tbl}.");
				return;
			}
		}

		// Coleções base
		$leadIds = DB::table(DC::TABLE_LEADS)->pluck('id')->all();
		if (!$leadIds) {
			$this->command?->warn('Nenhum Lead encontrado. Nada a semear para LeadDiscussion.');
			return;
		}

		$userIds = Schema::hasTable(DC::TABLE_USERS)
			? DB::table(DC::TABLE_USERS)->pluck('id')->all()
			: [];

		// Regra: sempre checar 'count' desta forma
		$hasCountOpt = ($this->command instanceof ArtisanCommand) && $this->command->hasOption('count');
		$target      = $hasCountOpt ? (int) ($this->command->option('count') ?? 0) : 0;

		// Se não informado, semear pelo menos 64 × (n de leads)
		if ($target <= 0) {
			$target = max(64 * count($leadIds), 64);
		}

		$inserted = 0;
		$now      = Carbon::now();

		// Helpers
		$maybe = static fn(int $pct, callable $fn) => (fake()->numberBetween(1, 100) <= $pct) ? $fn() : null;

		$makeAttachments = function (): array {
			$qtd = fake()->numberBetween(self::DEF_MIN_ATTACHES, self::DEF_MAX_ATTACHES);
			$out = [];
			for ($i = 0; $i < $qtd; $i++) {
				$ext   = Arr::random(['pdf', 'png', 'jpg', 'txt']);
				$mime  = [
					'pdf' => 'application/pdf',
					'png' => 'image/png',
					'jpg' => 'image/jpeg',
					'txt' => 'text/plain',
				][$ext];
				$name  = fake()->lexify("anexo-?????.{$ext}");
				$path  = 'lead_discussions/' . Str::uuid() . '.' . $ext;
				$out[] = [
					'name'        => $name,
					'path'        => $path,
					'mime'        => $mime,
					'size'        => fake()->numberBetween(5_000, 1_500_000),
					'uploaded_at' => Carbon::now()->subMinutes(fake()->numberBetween(0, 60 * 24))->toIso8601String(),
				];
			}
			return $out;
		};

		$makeReactions = function (array $users): array {
			$types = ['like', 'love', 'insight', 'question', 'flag'];
			$qtd   = fake()->numberBetween(0, self::DEF_MAX_REACTS);
			$out   = [];
			for ($i = 0; $i < $qtd; $i++) {
				$out[] = [
					'type' => Arr::random($types),
					'by'   => $users ? Arr::random($users) : null,
					'at'   => Carbon::now()->subMinutes(fake()->numberBetween(0, 60 * 72))->toIso8601String(),
				];
			}
			return $out;
		};

		$makeMetadata = function (): array {
			return array_filter([
				'ip'        => fake()->boolean(70) ? fake()->ipv4() : null,
				'ua'        => fake()->boolean(70) ? fake()->userAgent() : null,
				'lang'      => fake()->boolean(50) ? Arr::random(['pt-BR', 'en-US', 'es-ES']) : null,
				'referrer'  => fake()->boolean(30) ? fake()->url() : null,
				'thread'    => fake()->boolean(40) ? Str::uuid()->toString() : null,
			], fn($v) => $v !== null);
		};

		// Inserção batelada com transações curtas
		for ($start = 0; $start < $target; $start += self::BATCH_SIZE) {
			$left    = $target - $start;
			$current = min(self::BATCH_SIZE, $left);

			DB::transaction(function () use (
				$current,
				$leadIds,
				$userIds,
				$now,
				$maybe,
				$makeAttachments,
				$makeReactions,
				$makeMetadata
			) {
				for ($i = 0; $i < $current; $i++) {
					$leadId   = Arr::random($leadIds);
					$userId   = $maybe(65, fn() => $userIds ? Arr::random($userIds) : null);
					$uType    = Arr::random(UserType::values());

					$created  = $now->subDays(fake()->numberBetween(0, 90))
						->subMinutes(fake()->numberBetween(0, 1_440));
					$updated  = (clone $created)->addMinutes(fake()->numberBetween(0, 10_080));

					// Monta payload mínimo; arrays são passadas como array (Model normaliza)
					$payload = [
						PJC::COL_LD_ID          => $leadId,
						UC::COL_USER_ID         => $userId,
						UC::COL_U_TP            => $uType,
						'comment'               => fake()->paragraphs(fake()->numberBetween(1, 3), true),

						// Flags opcionais
						AC::COL_CAN_NADM_DL     => (bool) fake()->boolean(10),
						AC::COL_IS_FLAG         => (bool) fake()->boolean(8),
						AC::COL_IS_RPL          => (bool) fake()->boolean(18),
						AC::COL_IS_RPLD         => (bool) fake()->boolean(15),

						// Opcionais variados
						'label'                 => $maybe(25, fn() => Arr::random([
							'question',
							'clarification',
							'decision',
							'ops',
							'customer',
							'internal'
						])),
						'attachments'           => $maybe(45, $makeAttachments) ?? [],
						'reactions'             => $maybe(50, fn() => $makeReactions($userIds)) ?? [],
						'metadata'              => $maybe(55, $makeMetadata) ?? [],
					];

					/** @var LeadDiscussion $row */
					$row = LeadDiscussion::query()->create($payload);

					// Timestamps por atribuição direta (fora de mass assignment)
					$row->created_at = $created;
					$row->updated_at = $updated;

					// Auditoria opcional, se as colunas existirem (e sem mass assignment)
					if (Schema::hasColumn(DC::TABLE_LD_DSC, DC::COL_TABLE_CREATOR)) {
						$row->{DC::COL_TABLE_CREATOR} = $userId;
					}
					if (Schema::hasColumn(DC::TABLE_LD_DSC, DC::COL_TABLE_UPDATER)) {
						$row->{DC::COL_TABLE_UPDATER} = $userId;
					}

					$row->save();
				}
			});

			$inserted += $current;
		}

		$this->command?->info("LeadDiscussionSeeder: {$inserted} discussões inseridas em " . DC::TABLE_LD_DSC . ".");
	}
}
