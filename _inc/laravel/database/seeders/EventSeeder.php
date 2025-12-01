<?php

namespace Database\Seeders;

use App\Config\Constants\ActivitiesConstants as AC;
use App\Config\Constants\CompaniesConstants as CC;
use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ProjectsConstants as PJC;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\CarbonImmutable as Carbon;

class EventSeeder extends Seeder
{
	/**
	 * Parâmetros:
	 *  --count=N                 Quantidade de registros (padrão: 60)
	 *  EVENT_SEED_OPTIONALITY=x  Fração média de opcionais (0..1, padrão: 0.55)
	 */
	public function run(): void
	{
		$faker        = fake();
		$count        = (int) ($this->command?->option('count') ?? 60);
		$optionality  = (float) env('EVENT_SEED_OPTIONALITY', 0.55);
		$optionality  = max(0.0, min(1.0, $optionality)); // clamp

		// utilitários
		$p = fn(int $pct) => fake()->boolean(max(0, min(100, $pct)));
		$maybe = function (callable $producer) use ($optionality) {
			return fake()->boolean((int) round($optionality * 100)) ? $producer() : null;
		};
		$pickId = function (string $table): ?string {
			try {
				return DB::table($table)->inRandomOrder()->value('id');
			} catch (\Throwable) {
				return null;
			}
		};

		// tabelas-alvo
		$tables = [
			'users'       => DC::TABLE_USERS,
			'branches'    => DC::TABLE_BRANCHES ?? 'branches',
			'departments' => DC::TABLE_DEPARTMENTS,
			'employees'   => DC::TABLE_EMPLOYEES,
		];

		DB::transaction(function () use ($faker, $count, $maybe, $p, $pickId, $tables) {
			for ($i = 0; $i < $count; $i++) {
				// jitter de opcionalidade por registro (gera diversidade)
				$localMaybe = function (callable $producer) use ($maybe) {
					// leve variação por registro
					return $maybe($producer);
				};

				// datas/horários críveis (passado ou futuro próximo)
				$date = $localMaybe(function () {
					$days = fake()->numberBetween(-30, 60);
					return Carbon::now()->addDays($days)->format('Y-m-d');
				});

				$time = $localMaybe(function () {
					return sprintf('%02d:%02d:00', fake()->numberBetween(8, 20), Arr::random([0, 15, 30, 45]));
				});

				// durações coerentes (min ≤ exp ≤ max)
				$min = $localMaybe(fn() => (int) fake()->numberBetween(15, 60));
				$exp = $localMaybe(function () use ($min) {
					$base = $min ?? 15;
					return (int) fake()->numberBetween($base, $base + 60);
				});
				$max = $localMaybe(function () use ($exp, $min) {
					$floor = max($exp ?? ($min ?? 15), $min ?? 15);
					return (int) fake()->numberBetween($floor, $floor + 90);
				});

				// relacionais (preenche só se existirem)
				$companyId   = $localMaybe(fn() => $pickId($tables['users']));
				$branchId    = $localMaybe(fn() => $pickId($tables['branches']));
				$deptId      = $localMaybe(fn() => $pickId($tables['departments']));
				$employeeId  = $localMaybe(fn() => $pickId($tables['employees']));
				$responsible = $localMaybe(fn() => $pickId($tables['users']));

				// coleções opcionais
				$makePerson = function (?string $id = null): array {
					$name = fake()->name();
					$arr  = ['name' => $name];
					if ($id) {
						$arr['id'] = (string) $id;
					}
					if (fake()->boolean(50)) {
						$arr['email'] = fake()->safeEmail();
					}
					if (fake()->boolean(30)) {
						$arr['phone'] = fake()->numerify('+55###########');
					}
					return $arr;
				};

				$organizers = $localMaybe(function () use ($makePerson, $responsible) {
					$n = fake()->numberBetween(1, 3);
					$list = [];
					for ($i = 0; $i < $n; $i++) {
						$list[] = $makePerson();
					}
					// garante que o responsável (se existir) está entre organizadores por nome
					if ($responsible) {
						$list[] = $makePerson($responsible);
					}
					return $list;
				});

				$invited = $localMaybe(function () use ($makePerson) {
					$n = fake()->numberBetween(2, 8);
					return array_map(fn() => $makePerson(), range(1, $n));
				});

				$confirmed = $localMaybe(function () use ($invited) {
					// subset dos convidados quando possível
					if (is_array($invited) && $invited !== []) {
						return Arr::random($invited, fake()->numberBetween(1, max(1, (int) floor(count($invited) / 2))));
					}
					// fallback
					$n = fake()->numberBetween(1, 3);
					return array_map(fn() => [
						'name'  => fake()->name(),
						'email' => fake()->safeEmail(),
					], range(1, $n));
				});

				$gifts = $localMaybe(function () {
					$opts = ['Coffee', 'Snacks', 'Projector Adapter', 'Whiteboard Markers', 'Notebook'];
					$n = fake()->numberBetween(1, 3);
					return Arr::random($opts, $n);
				});

				$attachments = $localMaybe(function () {
					$n = fake()->numberBetween(1, 3);
					return array_map(fn() => [
						'name' => strtoupper(fake()->bothify('DOC-####')) . '.pdf',
						'url'  => fake()->url(),
					], range(1, $n));
				});

				$reminders = $localMaybe(function () {
					$kinds = ['email', 'sms', 'push'];
					$n = fake()->numberBetween(1, 3);
					$arr = [];
					for ($i = 0; $i < $n; $i++) {
						$arr[] = [
							'type'   => Arr::random($kinds),
							'before' => fake()->numberBetween(10, 120), // minutos
						];
					}
					return $arr;
				});

				$conditions = $localMaybe(function () {
					$pool = [
						'Bring ID',
						'No Photos',
						'NDA Required',
						'Arrive 10m earlier',
						'Smart Casual',
					];
					return Arr::random($pool, fake()->numberBetween(1, 3));
				});

				$tags = $localMaybe(function () {
					$pool = ['kickoff', 'training', 'support', 'demo', 'internal', 'external'];
					return Arr::random($pool, fake()->numberBetween(1, 3));
				});

				// montagem do payload (filtra nulls)
				$payload = array_filter([
					'id'                 => (string) Str::uuid(),
					'title'              => $localMaybe(fn() => fake()->sentence(4)),
					'date'               => $date,   // se nulo, DB usa default da migration
					'time'               => $time,   // idem
					CC::COL_DEP_ID       => $deptId,
					PJC::COL_MIN_DR      => $min,
					PJC::COL_EXP_DR      => $exp,
					PJC::COL_MAX_DR      => $max,
					'url'                => $localMaybe(fn() => fake()->url()),
					'location'           => $localMaybe(fn() => fake()->streetAddress()),
					'note'               => $localMaybe(fn() => fake()->realText(120)),
					CC::COL_IS_INT       => $localMaybe(fn() => fake()->boolean()),
					'attachments'        => $attachments,
					'invited'            => $invited,
					'conditions'         => $conditions,
					'reminders'          => $reminders,
					'tags'               => $tags,

					CC::COL_CP_ID        => $companyId,
					CC::COL_BRC_ID       => $branchId,
					\App\Config\Constants\UsersConstants::COL_EMP_ID => $employeeId,

					'responsible'        => $localMaybe(fn() => fake()->name()),
					AC::COL_RES_ID       => $responsible,
					'organizers'         => $organizers,
					'confirmed'          => $confirmed,
					'gifts'              => $gifts,

					'color'              => $localMaybe(fn() => Arr::random(['#3788d8', '#16a34a', '#ef4444', '#a855f7', '#f59e0b'])),
					'description'        => $localMaybe(fn() => fake()->realText(200)),
				], fn($v) => $v !== null);

				// Persistência direta: evita disparar booted()->saving() da model (que mexe em "participants")
				DB::table(DC::TABLE_EVENTS)->insert($payload);
			}
		});
	}
}
