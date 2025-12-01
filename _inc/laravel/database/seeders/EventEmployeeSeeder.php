<?php

namespace Database\Seeders;

use App\Config\Constants\ActivitiesConstants as AC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Enums\EventRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable as Carbon;

class EventEmployeeSeeder extends Seeder
{
	/**
	 * Opções:
	 *   --count=INT                    Limite aproximado de vínculos a criar (opcional).
	 *
	 * Variáveis de ambiente (opcionais):
	 *   EVENT_EMP_OPTIONALITY=0..1     Percentual médio de campos opcionais preenchidos (padrão 0.6).
	 *   EVENT_EMP_PER_EVENT_MIN=INT    Mín. vínculos por evento (padrão 2).
	 *   EVENT_EMP_PER_EVENT_MAX=INT    Máx. vínculos por evento (padrão 5).
	 */
	public function run(): void
	{
		if (
			!Schema::hasTable(DC::TABLE_EV_EMP) ||
			!Schema::hasTable(DC::TABLE_EVENTS) ||
			!Schema::hasTable(DC::TABLE_EMPLOYEES)
		) {
			$this->command?->warn('Tabelas necessárias não existem. Pulando EventEmployeeSeeder.');
			return;
		}

		$optionality = (float) env('EVENT_EMP_OPTIONALITY', 0.60);
		$optionality = max(0.0, min(1.0, $optionality)); // clamp

		$perEventMin = (int) env('EVENT_EMP_PER_EVENT_MIN', 2);
		$perEventMax = (int) env('EVENT_EMP_PER_EVENT_MAX', 5);
		if ($perEventMin < 0) {
			$perEventMin = 0;
		}
		if ($perEventMax < $perEventMin) {
			$perEventMax = $perEventMin;
		}

		$targetCount = (int) ($this->command?->option('count') ?? 0);

		// Helpers
		$maybe = fn(callable $producer) =>
		fake()->boolean((int) round($optionality * 100)) ? $producer() : null;

		$encode = fn($v) => $v === null ? null : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		// Base sets
		$eventIds = DB::table(DC::TABLE_EVENTS)->pluck('id')->all();
		$empIds   = DB::table(DC::TABLE_EMPLOYEES)->pluck('id')->all();
		$userIds  = Schema::hasTable(DC::TABLE_USERS) ? DB::table(DC::TABLE_USERS)->pluck('id')->all() : [];

		if (!$eventIds || !$empIds) {
			$this->command?->warn('Sem eventos ou empregados para vincular. Pulando.');
			return;
		}

		// Mapa existente para evitar duplicidade
		$existingPairs = DB::table(DC::TABLE_EV_EMP)
			->select(AC::COL_EV_ID, UC::COL_EMP_ID)
			->get()
			->reduce(function ($carry, $row) {
				$carry[$row->{AC::COL_EV_ID} . '|' . $row->{UC::COL_EMP_ID}] = true;
				return $carry;
			}, []);

		// Distribuição ponderada de papéis
		$roleBag = [
			EventRole::Responsible->value,
			EventRole::Organizer->value,
			EventRole::Organizer->value,
			EventRole::Sponsor->value,
			EventRole::Speaker->value,
			EventRole::Speaker->value,
			EventRole::Volunteer->value,
			EventRole::Attendee->value,
			EventRole::Attendee->value,
			EventRole::Attendee->value,
		];

		$now = Carbon::now();
		$rows = [];
		$totalPlanned = 0;

		foreach ($eventIds as $evId) {
			// Decide quantos vínculos para este evento
			$desired = fake()->numberBetween($perEventMin, $perEventMax);
			if ($targetCount > 0) {
				// Recalibra para não ultrapassar o alvo aproximado
				if ($totalPlanned + $desired > $targetCount) {
					$desired = max(0, $targetCount - $totalPlanned);
				}
			}
			if ($desired === 0) {
				continue;
			}

			// Seleciona N empregados distintos
			$poolCount = min($desired, count($empIds));
			$picked = (array) Arr::random($empIds, $poolCount);

			$hasResponsible = false;
			foreach ($picked as $idx => $empId) {
				$key = $evId . '|' . $empId;
				if (isset($existingPairs[$key])) {
					continue; // já existe, não recria
				}

				// Papel (às vezes nulo para acionar default da migration)
				$role = $maybe(function () use ($roleBag) {
					return Arr::random($roleBag);
				});

				// Garante no máx. um "Responsible" por evento
				if ($role === EventRole::Responsible->value) {
					if ($hasResponsible) {
						$role = Arr::random(array_values(array_filter(
							$roleBag,
							fn($r) => $r !== EventRole::Responsible->value
						)));
					} else {
						$hasResponsible = true;
					}
				}

				// Metadados variáveis por papel
				$metadata = $maybe(function () use ($role) {
					$base = [
						'note' => fake()->boolean(60) ? fake()->realText(80) : null,
						'tags' => fake()->boolean(40) ? Arr::random(
							['vip', 'remote', 'onsite', 'priority', 'backup', 'press', 'guest'],
							fake()->numberBetween(1, 3)
						) : null,
					];

					$roleExtras = match ($role) {
						EventRole::Speaker->value => [
							'topics'   => Arr::random(['SRE', 'DevOps', 'SecOps', 'FinOps', 'UX', 'DBA'], fake()->numberBetween(1, 3)),
							'slides'   => fake()->boolean(40) ? fake()->url() : null,
							'duration' => fake()->numberBetween(15, 50),
						],
						EventRole::Organizer->value, EventRole::Responsible->value => [
							'permissions' => Arr::random(['full', 'edit', 'view'], 1)[0],
							'channel'     => Arr::random(['email', 'chat', 'phone']),
						],
						EventRole::Sponsor->value => [
							'tier'       => Arr::random(['gold', 'silver', 'bronze']),
							'company'    => fake()->company(),
						],
						EventRole::Volunteer->value => [
							'duty'       => Arr::random(['registration', 'AV', 'logistics', 'guidance']),
							'shift'      => Arr::random(['morning', 'afternoon', 'evening']),
						],
						default => [
							'seat'       => fake()->boolean(30) ? fake()->numberBetween(1, 200) : null,
						],
					};

					// Remove nulls e retorna array
					return array_filter($base + $roleExtras, fn($v) => $v !== null && $v !== []);
				});

				// Audit (opcional)
				$creator = $maybe(function () use ($userIds) {
					return $userIds ? Arr::random($userIds) : null;
				});
				$updater = $maybe(function () use ($userIds) {
					return $userIds ? Arr::random($userIds) : null;
				});

				$createdAt = $now->subDays(fake()->numberBetween(0, 20))->subMinutes(fake()->numberBetween(0, 1440));
				$updatedAt = $createdAt->addMinutes(fake()->numberBetween(0, 1440));

				$rows[] = array_filter([
					'id'             => (string) Str::uuid(),
					AC::COL_EV_ID    => $evId,
					UC::COL_EMP_ID   => $empId,
					'role'           => $role,                            // pode ser null
					'metadata'       => $encode($metadata),              // JSON string ou null
					DC::COL_TABLE_CREATOR => $creator,                       // pode ser null
					DC::COL_TABLE_UPDATER => $updater,                       // pode ser null
					'created_at'     => $createdAt->toDateTimeString(),
					'updated_at'     => $updatedAt->toDateTimeString(),
				], fn($v) => $v !== null);

				$existingPairs[$key] = true;
				$totalPlanned++;

				if ($targetCount > 0 && $totalPlanned >= $targetCount) {
					break 2; // atingiu o limite global
				}
			}
		}

		if (!$rows) {
			$this->command?->info('Nenhum vínculo novo a inserir.');
			return;
		}

		// Upsert em chunks para respeitar UNIQUE (event_id, employee_id)
		DB::transaction(function () use ($rows) {
			$chunks = array_chunk($rows, 500);
			foreach ($chunks as $chunk) {
				DB::table(DC::TABLE_EV_EMP)->upsert(
					$chunk,
					[AC::COL_EV_ID, UC::COL_EMP_ID], // uniqueBy
					['role', 'metadata', DC::COL_TABLE_UPDATER, 'updated_at'] // update columns
				);
			}
		});

		$this->command?->info(sprintf(
			'EventEmployeeSeeder: %d vínculos processados (upsert).',
			$totalPlanned
		));
	}
}
