<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Enums\LeadRole;
use App\Models\UserLead;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserLeadSeeder extends Seeder
{
	// Parâmetros fixos (mocking) — NÃO usar env()
	private const BATCH_SIZE       = 1000;
	private const LOGS_MAX_ITEMS   = 3;   // entradas por registro
	private const ROLE_BIAS        = [
		// viés leve para perfis não-gerenciais
		'manager'      => 10,
		'supervisor'   => 12,
		'collaborator' => 46,
		'caller'       => 16,
		'callee'       => 10,
		'sponsor'      => 6,
	];

	public function run(): void
	{
		// 1) Sanidade de tabelas
		foreach ([DC::TABLE_USR_LD, DC::TABLE_LEADS, DC::TABLE_USERS] as $tbl) {
			if (!Schema::hasTable($tbl)) {
				$this->command?->warn("Seeder abortado: tabela ausente {$tbl}.");
				return;
			}
		}

		// 2) Coleções base
		$leadIds = DB::table(DC::TABLE_LEADS)->pluck('id')->all();
		if (!$leadIds) {
			$this->command?->warn('Nenhum Lead encontrado; nada a semear em user_leads.');
			return;
		}

		$userIds = DB::table(DC::TABLE_USERS)->pluck('id')->all();
		if (!$userIds) {
			$this->command?->warn('Nenhum User encontrado; nada a semear em user_leads.');
			return;
		}

		// 3) Determinação do target
		$hasCount = ($this->command instanceof Command) && $this->command->hasOption('count');
		$target   = $hasCount ? (int) ($this->command->option('count') ?? 0) : 0;

		if ($target <= 0) {
			// Base: nº de leads
			$target = max(64 * count($leadIds), 64);
		}

		// 4) Evitar duplicatas (par lead_id + user_id)
		$existingPairs = DB::table(DC::TABLE_USR_LD)
			->select(PJC::COL_LD_ID . ' as lead', UC::COL_USER_ID . ' as user')
			->get()
			->map(fn($r) => $r->lead . ':' . $r->user)
			->all();
		$used = array_fill_keys($existingPairs, true);

		$maxPossible = (count($leadIds) * count($userIds)) - count($existingPairs);
		if ($maxPossible <= 0) {
			$this->command?->warn('Todos os pares (lead × user) já existem. Nada a semear.');
			return;
		}
		if ($target > $maxPossible) {
			$this->command?->warn("Target ajustado de {$target} para {$maxPossible} (limite de combinações únicas disponíveis).");
			$target = $maxPossible;
		}

		// 5) Captura de possíveis logs (LeadActivityLog), se existir
		$fetchLogIds = (function () {
			$cls = '\\App\\Models\\LeadActivityLog';
			if (!class_exists($cls)) {
				return fn(string $leadId): array => [];
			}
			$model = new $cls;
			$table = method_exists($model, 'getTable') ? $model->getTable() : null;
			if (!$table || !Schema::hasTable($table)) {
				return fn(string $leadId): array => [];
			}
			$hasLeadFk = Schema::hasColumn($table, PJC::COL_LD_ID);
			return function (string $leadId) use ($table, $hasLeadFk): array {
				try {
					$q = DB::table($table);
					if ($hasLeadFk) {
						$q->where(PJC::COL_LD_ID, $leadId);
					}
					return $q->inRandomOrder()->limit(24)->pluck('id')->all();
				} catch (\Throwable) {
					return [];
				}
			};
		})();

		// 6) Geração
		$now      = Carbon::now();
		$inserted = 0;
		$roles    = LeadRole::values();

		// helper para escolher papel com viés
		$pickRole = function () use ($roles): string {
			$pool = [];
			foreach (self::ROLE_BIAS as $role => $weight) {
				for ($i = 0; $i < $weight; $i++) $pool[] = $role;
			}
			return Arr::random($pool ?: $roles);
		};

		// laço batelado
		for ($start = 0; $start < $target; $start += self::BATCH_SIZE) {
			$left    = $target - $start;
			$current = min(self::BATCH_SIZE, $left);

			DB::transaction(function () use (
				$current,
				$leadIds,
				$userIds,
				&$inserted,
				&$used,
				$pickRole,
				$fetchLogIds,
				$now
			) {
				$tries = 0;
				$limit = $current * 8; // margem para colisões de par

				while ($inserted < ($current + $inserted) && $tries < $limit) {
					$tries++;

					$leadId = Arr::random($leadIds);
					$userId = Arr::random($userIds);
					$key    = $leadId . ':' . $userId;

					if (isset($used[$key])) {
						continue; // já existe esse par
					}

					// Log candidates (opcionais, filtrados pelo Model)
					$logs   = [];
					if (fake()->boolean(45)) {
						$candidates = $fetchLogIds($leadId);
						if ($candidates) {
							$take = min(self::LOGS_MAX_ITEMS, count($candidates));
							$pick = (array) Arr::random($candidates, fake()->numberBetween(1, $take));
							foreach ((array) $pick as $logId) {
								$logs[] = [
									'id'  => (string) $logId,
									'tag' => Arr::random(['auto-link', 'evidence', 'audit']),
								];
							}
						}
					}

					// Datas variadas
					$created  = $now->subDays(fake()->numberBetween(0, 120))
						->subMinutes(fake()->numberBetween(0, 1_440));
					$updated  = (clone $created)->addMinutes(fake()->numberBetween(0, 20_160));

					// Montagem do payload
					$payload = [
						PJC::COL_LD_ID        => $leadId,
						UC::COL_USER_ID       => $userId,
						'role'                => $pickRole(),
						AC::COL_CAN_MK_DCS    => fake()->boolean(20), // Model ajusta para true se for Manager/Supervisor/Admin/SuperAdmin
						'logs'                => $logs,
					];

					/** @var UserLead $row */
					$row = UserLead::query()->create($payload);

					// Timestamps fora de mass assignment
					$row->created_at = $created;
					$row->updated_at = $updated;

					// Auditoria opcional (se existir no schema)
					if (Schema::hasColumn(DC::TABLE_USR_LD, DC::COL_TABLE_CREATOR)) {
						$row->{DC::COL_TABLE_CREATOR} = $userId;
					}
					if (Schema::hasColumn(DC::TABLE_USR_LD, DC::COL_TABLE_UPDATER)) {
						$row->{DC::COL_TABLE_UPDATER} = $userId;
					}

					$row->save();

					// marca par utilizado
					$used[$key] = true;
					$inserted++;
				}
			});
		}

		$this->command?->info("UserLeadSeeder: {$inserted} vínculos user×lead inseridos em " . DC::TABLE_USR_LD . ".");
	}
}
