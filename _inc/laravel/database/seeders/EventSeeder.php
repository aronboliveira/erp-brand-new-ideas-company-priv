<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	CompaniesConstants as CC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Models\Event;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
	private const OPTIONALITY = 0.55;

	/**
	 * Opções CLI:
	 *  --count=N   Quantidade de registros (padrão: 40)
	 */
	public function run(): void
	{
		$faker = fake();

		$defaultCount = 64;
		$count        = $defaultCount;

		if ($this->command instanceof Command && $this->command->hasOption('count')) {
			try {
				$raw = $this->command->option('count');
				if (is_numeric($raw) && (int) $raw > 0) {
					$count = (int) $raw;
				} else {
					$this->command->warn(sprintf(
						'EventSeeder: valor inválido para --count (%s); usando %d.',
						(string) $raw,
						$defaultCount
					));
				}
			} catch (\Throwable) {
				$this->command->warn('EventSeeder: falha ao ler opção --count; usando valor padrão.');
				$count = $defaultCount;
			}
		}

		$optionality = self::OPTIONALITY;

		$randBool = function (int $pct) use ($faker): bool {
			return $faker->boolean(max(0, min(100, $pct)));
		};

		$maybe = function (callable $producer) use ($randBool, $optionality) {
			return $randBool((int) round($optionality * 100)) ? $producer() : null;
		};

		$pickId = function (string $table): ?string {
			try {
				return DB::table($table)->inRandomOrder()->value('id');
			} catch (\Throwable) {
				return null;
			}
		};

		$tables = [
			'company'  => DC::TABLE_USERS,      // conforme migration (company_id -> users)
			'employee' => DC::TABLE_EMPLOYEES,  // ajuste se o nome real for outro
			'user'     => DC::TABLE_USERS,
		];

		$companySample = $pickId($tables['company']);
		if (!$companySample) {
			$this->command?->warn('EventSeeder: nenhuma linha em users/companies; nada foi gerado.');
			return;
		}

		DB::transaction(function () use (
			$faker,
			$count,
			$randBool,
			$maybe,
			$pickId,
			$tables
		): void {
			for ($i = 0; $i < $count; $i++) {
				$date = Carbon::now()
					->subDays($faker->numberBetween(0, 90))
					->format('Y-m-d');

				$time = $faker->time('H:i:s');

				$minDuration = $faker->numberBetween(30, 240); // minutos
				$expDuration = $randBool(70)
					? $faker->numberBetween($minDuration, $minDuration + 180)
					: null;

				$maxDuration = $randBool(40)
					? $faker->numberBetween(
						$expDuration ?? $minDuration,
						($expDuration ?? $minDuration) + 180
					)
					: null;

				$companyId     = $pickId($tables['company']);
				$employeeId    = $maybe(fn() => $pickId($tables['employee']));
				$responsible   = $maybe(fn() => $faker->name());
				$responsibleId = $maybe(fn() => $pickId($tables['user']));

				$attachments = $maybe(function () use ($faker) {
					$n     = $faker->numberBetween(0, 3);
					$items = [];
					for ($j = 0; $j < $n; $j++) {
						$items[] = [
							'name' => $faker->words(3, true) . '.pdf',
							'url'  => $faker->url(),
						];
					}
					return $items;
				});

				$invited = $maybe(function () use ($faker) {
					$n     = $faker->numberBetween(0, 8);
					$items = [];
					for ($j = 0; $j < $n; $j++) {
						$items[] = [
							'name'   => $faker->name(),
							'email'  => $faker->safeEmail(),
							'phone'  => $faker->boolean(70) ? $faker->e164PhoneNumber() : null,
							'locale' => $faker->randomElement(['pt_BR', 'en', 'es', 'de', 'fr']),
						];
					}
					return $items;
				});

				$conditions = $maybe(function () use ($faker) {
					return [
						'dress_code' => $faker->randomElement(['casual', 'business', 'formal']),
						'contact'    => $faker->randomElement([
							$faker->safeEmail(),
							$faker->e164PhoneNumber(),
						]),
					];
				});

				$organizers = $maybe(function () use ($faker, $employeeId) {
					$n     = $faker->numberBetween(1, 4);
					$items = [];
					for ($j = 0; $j < $n; $j++) {
						$items[] = [
							'id'      => $faker->boolean(60) ? (string) Str::uuid() : null,
							'name'    => $faker->name(),
							'email'   => $faker->boolean(70) ? $faker->safeEmail() : null,
							'phone'   => $faker->boolean(70) ? $faker->e164PhoneNumber() : null,
							'contact' => $faker->boolean(30)
								? $faker->safeEmail()
								: $faker->e164PhoneNumber(),
							'type'    => $faker->randomElement(['employee', 'user', 'external']),
						];
					}

					if ($employeeId) {
						$items[] = [
							'id'      => (string) $employeeId,
							'name'    => null,
							'phone'   => $faker->boolean(70) ? $faker->e164PhoneNumber() : null,
							'type'    => 'employee',
							'contact' => $faker->boolean(40)
								? $faker->safeEmail()
								: $faker->e164PhoneNumber(),
						];
					}

					return $items;
				});

				$confirmed = $maybe(function () use ($faker) {
					$n     = $faker->numberBetween(0, 6);
					$items = [];
					for ($j = 0; $j < $n; $j++) {
						$items[] = [
							'name'      => $faker->name(),
							'email'     => $faker->boolean(70) ? $faker->safeEmail() : null,
							'phone'     => $faker->boolean(70) ? $faker->e164PhoneNumber() : null,
							'contact'   => $faker->boolean(40)
								? $faker->safeEmail()
								: $faker->e164PhoneNumber(),
							'confirmed' => true,
						];
					}
					return $items;
				});

				$gifts = $maybe(function () use ($faker) {
					$n     = $faker->numberBetween(0, 4);
					$items = [];
					for ($j = 0; $j < $n; $j++) {
						$items[] = [
							'item'     => $faker->word(),
							'quantity' => $faker->numberBetween(1, 20),
							'contact'  => $faker->randomElement([
								$faker->safeEmail(),
								$faker->e164PhoneNumber(),
							]),
						];
					}
					return $items;
				});

				$sponsors = $maybe(function () use ($faker) {
					$n     = $faker->numberBetween(0, 3);
					$items = [];
					for ($j = 0; $j < $n; $j++) {
						$items[] = [
							'name'    => $faker->company(),
							'email'   => $faker->boolean(60) ? $faker->companyEmail() : null,
							'phone'   => $faker->boolean(60) ? $faker->e164PhoneNumber() : null,
							'contact' => $faker->randomElement([
								$faker->safeEmail(),
								$faker->e164PhoneNumber(),
							]),
						];
					}
					return $items;
				});

				$tags = $maybe(function () use ($faker) {
					return $faker->words($faker->numberBetween(1, 4));
				});

				Event::query()->create([
					'id'                     => (string) Str::uuid(),
					'title'                  => $faker->sentence(4),
					'date'                   => $date,
					'time'                   => $time,
					CC::COL_DEP_ID          => null,
					PJC::COL_MIN_DR         => $minDuration,
					PJC::COL_EXP_DR         => $expDuration,
					PJC::COL_MAX_DR         => $maxDuration,
					'url'                    => $faker->url(),
					'location'               => $faker->address(),
					'note'                   => $maybe(fn() => $faker->sentence(10)),
					CC::COL_IS_INT          => $faker->boolean(40),
					'attachments'            => $attachments,
					'invited'                => $invited,
					'conditions'             => $conditions,
					'reminders'              => null,
					'tags'                   => $tags,
					CC::COL_CP_ID           => $companyId,
					UC::COL_EMP_ID          => $employeeId,
					'responsible'            => $responsible,
					AC::COL_RES_ID          => $responsibleId,
					'organizers'             => $organizers,
					'confirmed'              => $confirmed,
					'gifts'                  => $gifts,
					'sponsors'               => $sponsors,
					// participants é montado no saving() pelo model
					'color'                  => $faker->randomElement([
						'#3788d8',
						'#22c55e',
						'#f97316',
						'#ef4444',
					]),
					'description'            => $maybe(fn() => $faker->paragraph()),
				]);
			}
		});
	}
}
