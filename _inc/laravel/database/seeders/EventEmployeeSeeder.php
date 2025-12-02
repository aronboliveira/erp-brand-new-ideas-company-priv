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
	// Parâmetros fixos (sem env)
	private const OPTIONALITY      = 0.60; // antes: EVENT_EMP_OPTIONALITY
	private const PER_EVENT_MIN    = 2;    // antes: EVENT_EMP_PER_EVENT_MIN
	private const PER_EVENT_MAX    = 5;    // antes: EVENT_EMP_PER_EVENT_MAX

	/**
	 * Opções CLI:
	 *   --count=INT   Limite aproximado de vínculos a criar (opcional).
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

		$optionality = self::OPTIONALITY;
		$perEventMin = self::PER_EVENT_MIN;
		$perEventMax = self::PER_EVENT_MAX;

		$targetCount = (int) ($this->command && $this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count') ? $this->command?->option('count') : 64);

		$maybe = fn(callable $producer) =>
		fake()->boolean((int) round($optionality * 100)) ? $producer() : null;

		$encode = fn($v) => $v === null ? null : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

		$eventIds = DB::table(DC::TABLE_EVENTS)->pluck('id')->all();
		$empIds   = DB::table(DC::TABLE_EMPLOYEES)->pluck('id')->all();
		$userIds  = Schema::hasTable(DC::TABLE_USERS) ? DB::table(DC::TABLE_USERS)->pluck('id')->all() : [];

		if (!$eventIds || !$empIds) {
			$this->command?->warn('Sem eventos ou empregados para vincular. Pulando.');
			return;
		}

		$existingPairs = DB::table(DC::TABLE_EV_EMP)
			->select(AC::COL_EV_ID, UC::COL_EMP_ID)
			->get()
			->reduce(function ($carry, $row) {
				$carry[$row->{AC::COL_EV_ID} . '|' . $row->{UC::COL_EMP_ID}] = true;
				return $carry;
			}, []);

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
			$desired = fake()->numberBetween($perEventMin, $perEventMax);
			if ($targetCount > 0 && $totalPlanned + $desired > $targetCount) {
				$desired = max(0, $targetCount - $totalPlanned);
			}
			if ($desired === 0) continue;

			$picked = (array) Arr::random($empIds, min($desired, count($empIds)));

			$hasResponsible = false;
			foreach ($picked as $empId) {
				$key = $evId . '|' . $empId;
				if (isset($existingPairs[$key])) continue;

				$role = $maybe(fn() => Arr::random($roleBag));
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
							'permissions' => Arr::random(['full', 'edit', 'view']),
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

					return array_filter($base + $roleExtras, fn($v) => $v !== null && $v !== []);
				});

				$creator = $maybe(fn() => $userIds ? Arr::random($userIds) : null);
				$updater = $maybe(fn() => $userIds ? Arr::random($userIds) : null);

				$createdAt = $now->subDays(fake()->numberBetween(0, 20))->subMinutes(fake()->numberBetween(0, 1440));
				$updatedAt = $createdAt->addMinutes(fake()->numberBetween(0, 1440));

				$rows[] = array_filter([
					'id'                  => (string) Str::uuid(),
					AC::COL_EV_ID         => $evId,
					UC::COL_EMP_ID        => $empId,
					'role'                => $role,
					'metadata'            => $encode($metadata),
					DC::COL_TABLE_CREATOR => $creator,
					DC::COL_TABLE_UPDATER => $updater,
					'created_at'          => $createdAt->toDateTimeString(),
					'updated_at'          => $updatedAt->toDateTimeString(),
				], fn($v) => $v !== null);

				$existingPairs[$key] = true;
				$totalPlanned++;

				if ($targetCount > 0 && $totalPlanned >= $targetCount) break 2;
			}
		}

		if (!$rows) {
			$this->command?->info('Nenhum vínculo novo a inserir.');
			return;
		}

		DB::transaction(function () use ($rows) {
			foreach (array_chunk($rows, 500) as $chunk) {
				DB::table(DC::TABLE_EV_EMP)->upsert(
					$chunk,
					[AC::COL_EV_ID, UC::COL_EMP_ID],
					['role', 'metadata', DC::COL_TABLE_UPDATER, 'updated_at']
				);
			}
		});

		$this->command?->info(sprintf(
			'EventEmployeeSeeder: %d vínculos processados (upsert).',
			$totalPlanned
		));
	}
}
