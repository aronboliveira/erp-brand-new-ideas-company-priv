<?php

namespace Database\Seeders;

use App\Config\Constants\ActivitiesConstants as AC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\UsersConstants as UC;
use App\Enums\EventRole;
use App\Models\Employee;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class EventEmployeeSeeder extends Seeder
{
	// Parâmetros fixos (sem env)
	private const OPTIONALITY   = 0.60; // probabilidade de preencher campos opcionais
	private const PER_EVENT_MIN = 2;
	private const PER_EVENT_MAX = 5;

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

		// Bases
		$eventIds = DB::table(DC::TABLE_EVENTS)->pluck('id')->all();
		$empIds   = DB::table(DC::TABLE_EMPLOYEES)->pluck('id')->all();
		$userIds  = Schema::hasTable(DC::TABLE_USERS)
			? DB::table(DC::TABLE_USERS)->pluck('id')->all()
			: [];

		if (!$eventIds || !$empIds) {
			$this->command?->warn('Sem eventos ou empregados para vincular. Pulando.');
			return;
		}

		// Política de quantidade: por padrão, pelo menos 64 * #eventos, limitado ao total possível de pares
		$defaultTarget = max(64 * count($eventIds), 64);
		$absoluteMax   = count($eventIds) * count($empIds);

		$targetCount = $defaultTarget;
		if ($this->command instanceof Command && $this->command->hasOption('count')) {
			$raw = $this->command->option('count');
			if (is_numeric($raw) && (int) $raw > 0) {
				$targetCount = (int) $raw;
			} else {
				$this->command->warn("EventEmployeeSeeder: valor inválido para --count ({$raw}); usando {$defaultTarget}.");
			}
		}
		// original: $targetCount = min($targetCount, $absoluteMax);
		$HARD_CAP = 2;
		$targetCount = min($HARD_CAP, $targetCount, $absoluteMax);

		// Helpers
		$maybe  = fn(callable $producer) => fake()->boolean((int) round(self::OPTIONALITY * 100)) ? $producer() : null;
		$encode = fn($v) => $v === null ? null : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		// Evitar duplicidades com dados existentes
		$existingPairs = DB::table(DC::TABLE_EV_EMP)
			->select(AC::COL_EV_ID, UC::COL_EMP_ID)
			->get()
			->reduce(function (array $carry, $row) {
				$carry[$row->{AC::COL_EV_ID}][$row->{UC::COL_EMP_ID}] = true;
				return $carry;
			}, []);

		// Saco de papéis com pesos
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

		$rows          = [];
		$totalPlanned  = 0;
		$now           = Carbon::now();

		// Para distribuir melhor, embaralhe a ordem dos eventos
		$eventsShuffled = $eventIds;
		shuffle($eventsShuffled);

		// Geração: passa por eventos enquanto houver necessidade
		while ($totalPlanned < $targetCount) {
			try {
				$madeProgress = false;

				foreach ($eventsShuffled as $evId) {
					if ($totalPlanned >= $targetCount) {
						break;
					}

					// Conjunto de empregados ainda não vinculados a este evento
					$already = $existingPairs[$evId] ?? [];
					$availableEmpIds = array_values(array_diff($empIds, array_keys($already)));
					if (!$availableEmpIds) {
						continue; // este evento esgotou combinações possíveis
					}

					$desired = fake()->numberBetween(self::PER_EVENT_MIN, self::PER_EVENT_MAX);
					$remainingGlobal = $targetCount - $totalPlanned;
					$desired = min($desired, $remainingGlobal, count($availableEmpIds));

					// Garante no máximo 1 responsável por evento nesta rodada
					$hasResponsible = false;

					// Escolhe N empregados distintos para este evento
					$picked = (array) Arr::random($availableEmpIds, $desired);

					foreach ($picked as $empId) {
						// Papel (fallback para Attendee para manter consistência)
						$role = $maybe(fn() => Arr::random($roleBag)) ?? EventRole::Attendee->value;

						if ($role === EventRole::Responsible->value) {
							if ($hasResponsible) {
								// troque por outro papel se já houver responsável
								$role = Arr::random(array_values(array_filter(
									$roleBag,
									fn($r) => $r !== EventRole::Responsible->value
								)));
							} else {
								$hasResponsible = true;
							}
						}

						// Metadata específica por papel
						$metadata = $maybe(function () use ($role) {
							$base = [
								'note' => fake()->boolean(60) ? fake()->realText(80) : null,
								'tags' => fake()->boolean(40)
									? Arr::random(
										['vip', 'remote', 'onsite', 'priority', 'backup', 'press', 'guest'],
										fake()->numberBetween(1, 3)
									)
									: null,
							];

							$roleExtras = match ($role) {
								EventRole::Speaker->value => [
									'topics'   => Arr::random(['SRE', 'DevOps', 'SecOps', 'FinOps', 'UX', 'DBA'], fake()->numberBetween(1, 3)),
									'slides'   => fake()->boolean(40) ? fake()->url() : null,
									'duration' => fake()->numberBetween(15, 50),
								],
								EventRole::Organizer->value, EventRole::Responsible->value => [
									'permissions' => Arr::random(['full', 'edit', 'view']),
									'channel'     => Arr::random(['email', 'chat', 'phone']),
								],
								EventRole::Sponsor->value => [
									'tier'    => Arr::random(['gold', 'silver', 'bronze']),
									'company' => fake()->company(),
								],
								EventRole::Volunteer->value => [
									'duty'  => Arr::random(['registration', 'AV', 'logistics', 'guidance']),
									'shift' => Arr::random(['morning', 'afternoon', 'evening']),
								],
								default => [
									'seat' => fake()->boolean(30) ? fake()->numberBetween(1, 200) : null,
								],
							};

							return array_filter($base + $roleExtras, fn($v) => $v !== null && $v !== []);
						});

						// Auditoria (sempre com chaves presentes; valores podem ser null)
						$creator   = $maybe(fn() => $userIds ? Arr::random($userIds) : null);
						$updater   = $maybe(fn() => $userIds ? Arr::random($userIds) : null);
						$createdAt = $now
							->subDays(fake()->numberBetween(0, 20))
							->subMinutes(fake()->numberBetween(0, 1440));
						$updatedAt = $createdAt->addMinutes(fake()->numberBetween(0, 1440));

						// Linha CONSISTENTE: mesmas colunas em todas as linhas
						$rows[] = [
							'id'                  => (string) Str::uuid(),
							AC::COL_EV_ID         => $evId,
							UC::COL_EMP_ID        => $empId,
							'role'                => $role,
							'metadata'            => $encode($metadata),
							DC::COL_TABLE_CREATOR => $creator,
							DC::COL_TABLE_UPDATER => $updater,
							'created_at'          => $createdAt->toDateTimeString(),
							'updated_at'          => $updatedAt->toDateTimeString(),
						];

						// Marcar par como usado
						$existingPairs[$evId][$empId] = true;
						// $ref = $empId instanceof Employee ? ($empId->name ?? $empId->id) : (Employee::query()->where('id', $empId)->value('name') ?? $empId);
						// (new \Symfony\Component\Console\Output\ConsoleOutput
						// )->writeln("Criando Funcionário em Chamada para event={$evId} employee={$ref} como " . $role);
						$totalPlanned++;
						$madeProgress = true;

						if ($totalPlanned >= $targetCount) {
							break 2;
						}
					}
				}

				// Não há mais combinações possíveis para atender ao alvo
				if (!$madeProgress) {
					break;
				}
			} catch (\Exception $e) {
				$totalPlanned++;
				Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
				continue;
			}
		}

		if (!$rows) {
			$this->command?->info('Nenhum vínculo novo a inserir.');
			return;
		}

		// Upsert em chunks
		DB::transaction(function () use ($rows) {
			foreach (array_chunk($rows, 500) as $chunk) {
				DB::table(DC::TABLE_EV_EMP)->upsert(
					$chunk,
					[AC::COL_EV_ID, UC::COL_EMP_ID],
					['role', 'metadata', DC::COL_TABLE_UPDATER, 'updated_at']
				);
			}
		});

		$this->command?->info("EventEmployeeSeeder: {$totalPlanned} vínculos processados (upsert).");
	}
}
