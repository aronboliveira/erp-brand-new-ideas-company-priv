<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	CompaniesConstants as CC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Models\Department;
use App\Models\Event;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class EventSeeder extends Seeder
{
	// Probabilidade base (0–1) de tornar um campo opcional nulo
	private const OPTIONALITY = 0.55;

	// Chance (0–1) de forçar um valor nulo para department_id (tolerância a null)
	private const DEPT_NULL_TOLERANCE = 0.12;

	/**
	 * CLI:
	 *  --count=N   Quantidade de registros (padrão: 64)
	 */
	public function run(): void
	{
		$faker = fake();

		// --------- Pré-checagens defensivas ---------
		foreach ([Event::query()->getModel()->getTable(), DC::TABLE_USERS] as $tbl) {
			if (!Schema::hasTable($tbl)) {
				$this->command?->warn("EventSeeder: tabela ausente: {$tbl}. Abortado.");
				return;
			}
		}

		$hasBranchesTable    = Schema::hasTable(DC::TABLE_BRANCHES);
		$hasDepartmentsTable = Schema::hasTable(DC::TABLE_DEPARTMENTS);

		if (!$hasBranchesTable) {
			$this->command?->warn('EventSeeder: tabela de branches ausente; seguirei com branch_id nulo.');
		}

		if (!$hasDepartmentsTable) {
			$this->command?->warn('EventSeeder: tabela de departments ausente; department_id ficará sempre nulo.');
		}

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
				$this->command?->warn('EventSeeder: falha ao ler --count; usando valor padrão.');
				$count = $defaultCount;
			}
		}

		// --------- Utilitários de aleatoriedade ---------
		$randBool = function (int $pct) use ($faker): bool {
			return $faker->boolean(max(0, min(100, $pct)));
		};
		$maybe = function (callable $producer) use ($randBool) {
			return $randBool((int) round(self::OPTIONALITY * 100)) ? $producer() : null;
		};

		// --------- Coleção de branches ---------
		$branches = collect();

		if ($hasBranchesTable) {
			$branchCols = ['id'];
			if (Schema::hasColumn(DC::TABLE_BRANCHES, 'departments')) {
				$branchCols[] = 'departments';
			}
			if (Schema::hasColumn(DC::TABLE_BRANCHES, CC::COL_BRC_NM)) {
				$branchCols[] = CC::COL_BRC_NM;
			}

			try {
				$branches = DB::table(DC::TABLE_BRANCHES)->select($branchCols)->get();
			} catch (\Throwable $e) {
				$this->command?->warn('EventSeeder: falha ao consultar branches: ' . $e->getMessage());
			}
		}

		// --------- Departments (IDs + mapa por nome) ---------
		$allDepartmentIds = [];
		$deptNameToId     = [];

		if ($hasDepartmentsTable) {
			try {
				$rows = Department::query()
					->select('id', 'name')
					->get();

				foreach ($rows as $row) {
					if (!empty($row->id)) {
						$allDepartmentIds[] = $row->id;
						if (!empty($row->name)) {
							$deptNameToId[$row->name] = $row->id;
						}
					}
				}

				$allDepartmentIds = array_values(array_unique($allDepartmentIds));
			} catch (\Throwable $e) {
				$this->command?->warn('EventSeeder: falha ao consultar departments: ' . $e->getMessage());
			}
		}

		// --------- Helpers de FK opcionais ---------
		$pickId = function (string $table): ?string {
			if (!Schema::hasTable($table)) {
				return null;
			}
			try {
				return DB::table($table)->inRandomOrder()->value('id');
			} catch (\Throwable) {
				return null;
			}
		};

		// Mapeia tabelas conhecidas
		$tables = [
			'company'  => DC::TABLE_USERS,     // conforme migração legada (company_id -> users)
			'employee' => DC::TABLE_EMPLOYEES, // ajuste se necessário
			'user'     => DC::TABLE_USERS,
		];

		// Verifica se há ao menos uma "empresa" para FK (company_id)
		$companySample = $pickId($tables['company']);
		if (!$companySample) {
			$this->command?->warn('EventSeeder: nenhuma linha em users (company_id); nada foi gerado.');
			return;
		}

		// --------- Inserção ---------
		DB::transaction(function () use (
			$faker,
			$count,
			$randBool,
			$maybe,
			$pickId,
			$tables,
			$branches,
			$hasBranchesTable,
			$hasDepartmentsTable,
			$allDepartmentIds,
			$deptNameToId
		): void {
			for ($i = 0; $i < $count; $i++) {
				$date = Carbon::now()
					->subDays($faker->numberBetween(0, 120))
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

				// FKs opcionais
				$companyId     = $pickId($tables['company']);
				$employeeId    = $maybe(fn() => $pickId($tables['employee']));
				$responsible   = $maybe(fn() => $faker->name());
				$responsibleId = $maybe(fn() => $pickId($tables['user']));

				// --------- Branch + Departments (nova lógica) ---------
				$branchId      = null;
				$departmentId  = null;
				$branchDeptsRaw = null;

				if ($hasBranchesTable && $branches->count() > 0 && $faker->boolean(75)) {
					$chosenBranch   = $branches->random();
					$branchId       = $chosenBranch->id ?? null;
					$branchDeptsRaw = $chosenBranch->departments ?? null;
				}

				if ($hasDepartmentsTable && !empty($allDepartmentIds)) {
					if ($branchId !== null) {
						// Branch definido → usa departments do próprio branch
						$departmentId = $this->resolveDepartmentFromBranch(
							$branchDeptsRaw,
							$allDepartmentIds,
							$deptNameToId
						);
					} else {
						// Branch nulo → escolhe entre todos os departments existentes
						$departmentId = Arr::random($allDepartmentIds);
					}

					// Tolerância a null em department_id (ainda que raramente)
					if (
						$departmentId !== null
						&& $faker->boolean((int) round(self::DEPT_NULL_TOLERANCE * 100))
					) {
						$departmentId = null;
					}
				}

				// --------- Campos “complexos” opcionais ---------
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
							'email'  => $faker->boolean(75) ? $faker->safeEmail() : null,
							'phone'  => $faker->boolean(65) ? $faker->e164PhoneNumber() : null,
							'locale' => $faker->randomElement(['pt_BR', 'en', 'es', 'de', 'fr']),
						];
					}
					return $items;
				});

				$conditions = $maybe(function () use ($faker) {
					return [
						'dress_code' => $faker->randomElement(['casual', 'business', 'formal']),
						'contact'    => $faker->randomElement([$faker->safeEmail(), $faker->e164PhoneNumber()]),
					];
				});

				$organizers = $maybe(function () use ($faker, $employeeId) {
					$n     = $faker->numberBetween(1, 4);
					$items = [];
					for ($j = 0; $j < $n; $j++) {
						$items[] = [
							'id'      => $faker->boolean(55) ? (string) Str::uuid() : null,
							'name'    => $faker->name(),
							'email'   => $faker->boolean(70) ? $faker->safeEmail() : null,
							'phone'   => $faker->boolean(70) ? $faker->e164PhoneNumber() : null,
							'contact' => $faker->boolean(40) ? $faker->safeEmail() : $faker->e164PhoneNumber(),
							'type'    => $faker->randomElement(['employee', 'user', 'external']),
						];
					}
					if ($employeeId) {
						$items[] = [
							'id'      => (string) $employeeId,
							'name'    => null,
							'phone'   => $faker->boolean(70) ? $faker->e164PhoneNumber() : null,
							'type'    => 'employee',
							'contact' => $faker->boolean(45) ? $faker->safeEmail() : $faker->e164PhoneNumber(),
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
							'contact'   => $faker->boolean(40) ? $faker->safeEmail() : $faker->e164PhoneNumber(),
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
							'contact'  => $faker->randomElement([$faker->safeEmail(), $faker->e164PhoneNumber()]),
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
							'contact' => $faker->randomElement([$faker->safeEmail(), $faker->e164PhoneNumber()]),
						];
					}
					return $items;
				});

				$tags = $maybe(fn() => $faker->words($faker->numberBetween(1, 4)));

				// Lembretes variáveis
				$reminders = $maybe(function () use ($faker) {
					$n     = $faker->numberBetween(0, 3);
					$items = [];
					for ($j = 0; $j < $n; $j++) {
						$items[] = [
							'offset_minutes' => $faker->randomElement([5, 10, 15, 30, 60, 120]),
							'channel'        => $faker->randomElement(['email', 'sms', 'push']),
						];
					}
					return $items;
				});

				Event::query()->create([
					'id'                      => (string) Str::uuid(),
					'title'                   => $faker->sentence(4),
					'date'                    => $date,
					'time'                    => $time,
					CC::COL_DEP_ID           => $departmentId,
					PJC::COL_MIN_DR          => $minDuration,
					PJC::COL_EXP_DR          => $expDuration,
					PJC::COL_MAX_DR          => $maxDuration,
					'url'                     => $faker->url(),
					'location'                => $faker->address(),
					'note'                    => $maybe(fn() => $faker->sentence(10)),
					CC::COL_IS_INT           => $faker->boolean(40),
					'attachments'             => $attachments,
					'invited'                 => $invited,
					'conditions'              => $conditions,
					'reminders'               => $reminders,
					'tags'                    => $tags,
					CC::COL_CP_ID            => $companyId,
					CC::COL_BRC_ID           => $branchId,
					UC::COL_EMP_ID           => $employeeId,
					'responsible'             => $responsible,
					AC::COL_RES_ID           => $responsibleId,
					'organizers'              => $organizers,
					'confirmed'               => $confirmed,
					'gifts'                   => $gifts,
					'sponsors'                => $sponsors,
					// participants é montado no saving() pelo model
					'color'                   => $faker->randomElement(['#3788d8', '#22c55e', '#f97316', '#ef4444']),
					'description'             => $maybe(fn() => $faker->paragraph()),
				]);
			}
		});
	}

	/**
	 * Resolve um único department_id para o evento, a partir do campo 'departments'
	 * do branch (lista de nomes ou UUIDs) e do catálogo de departments.
	 *
	 * Regras:
	 *  - Se todos tokens forem UUIDs → valida contra a tabela e sorteia 1.
	 *  - Se houver nomes → faz lookup pelo Departments model usando 'name'
	 *    e sorteia 1 dos IDs encontrados.
	 *  - Se nada bater → sorteia 1 entre todos os IDs existentes.
	 */
	private function resolveDepartmentFromBranch(
		mixed $branchDepartments,
		array $allDepartmentIds,
		array $deptNameToId
	): ?string {
		if (empty($allDepartmentIds)) {
			return null;
		}

		$tokens = $this->parseDepartments($branchDepartments);
		if (empty($tokens)) {
			return Arr::random($allDepartmentIds);
		}

		$uuidCandidates = [];
		$nameCandidates = [];

		foreach ($tokens as $rawToken) {
			$token = trim((string) $rawToken);
			if ($token === '') {
				continue;
			}

			$looksUuid = false;
			try {
				if (class_exists('\\Utility') && method_exists('\\Utility', 'looksLikeUuid')) {
					$looksUuid = (bool) \Utility::looksLikeUuid($token);
				}
			} catch (\Throwable) {
				$looksUuid = false;
			}

			if ($looksUuid) {
				$uuidCandidates[] = $token;
			} else {
				$nameCandidates[] = $token;
			}
		}

		$ids = [];

		// UUIDs → valida contra a tabela
		if ($uuidCandidates) {
			try {
				$valid = DB::table(DC::TABLE_DEPARTMENTS)
					->whereIn('id', $uuidCandidates)
					->pluck('id')
					->all();
				$ids = array_merge($ids, $valid);
			} catch (\Throwable) {
				// ignora e cai no fallback
			}
		}

		// Nomes → lookup via Departments model
		if ($nameCandidates && $deptNameToId) {
			foreach ($nameCandidates as $name) {
				if (isset($deptNameToId[$name])) {
					$ids[] = $deptNameToId[$name];
				}
			}
		}

		$ids = array_values(array_unique(array_filter($ids)));

		if (empty($ids)) {
			return Arr::random($allDepartmentIds);
		}

		return Arr::random($ids);
	}

	/**
	 * Parser robusto de 'departments':
	 * - Tenta JSON; se objeto, extrai campos id/uuid/department_id/name/name-like;
	 * - Se JSON falhar, divide por vírgula, ponto-e-vírgula, pipe ou quebra de linha.
	 * Retorna uma lista de strings (ids ou nomes).
	 */
	private function parseDepartments(mixed $raw): array
	{
		if ($raw === null) return [];

		$str = is_string($raw) ? trim($raw) : (is_scalar($raw) ? (string) $raw : '');
		if ($str !== '') {
			if ((str_starts_with($str, '[') && str_ends_with($str, ']')) ||
				(str_starts_with($str, '{') && str_ends_with($str, '}'))
			) {
				$decoded = json_decode($str, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					// Array simples
					if (is_array($decoded) && array_keys($decoded) === range(0, count($decoded) - 1)) {
						return $this->extractDeptTokensFromArray($decoded);
					}
					// Objeto único
					if (is_array($decoded)) {
						return $this->extractDeptTokensFromArray([$decoded]);
					}
				}
			}
		}

		// CSV / lista livre
		$parts = preg_split('/[\s]*[,;\|\n\r]+[\s]*/u', (string) $raw) ?: [];
		$parts = array_values(array_filter(array_map(fn($v) => trim((string) $v), $parts)));
		return $parts;
	}

	/**
	 * Extrai tokens de uma lista (strings ou arrays/objetos) priorizando ID/UUID.
	 */
	private function extractDeptTokensFromArray(array $arr): array
	{
		$out = [];
		foreach ($arr as $item) {
			if (is_string($item)) {
				$tok = trim($item);
				if ($tok !== '') $out[] = $tok;
				continue;
			}
			if (is_array($item)) {
				// Prioridade: id-like → name-like
				$candidates = [
					'id',
					'uuid',
					'department_id',
					'dep_id',
					'code',
					'name',
					'nome',
					'label',
					'slug',
				];
				foreach ($candidates as $key) {
					if (!empty($item[$key]) && is_scalar($item[$key])) {
						$tok = trim((string) $item[$key]);
						if ($tok !== '') {
							$out[] = $tok;
							break;
						}
					}
				}
			}
		}
		return $out;
	}
}
