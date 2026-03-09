<?php

namespace Database\Seeders;

use App\Config\Constants\{
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	BillsConstants as BC,
	ActivitiesConstants as AC
};
use App\Enums\EvaluationStatus;
use App\Models\{
	Deal as Dl,
	User as Usr,
	Pipeline as Pln,
	Stage as Stg,
	Label as Lbl,
	Source as Src,
	ProductService as Psv
};
use App\Traits\EnsuresSystemUser;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log, Schema};
use Illuminate\Support\Str;

final class DealSeeder extends Seeder
{
	use EnsuresSystemUser;

	// “Low to none optional fields”: ~5% de chance de null
	private const OPTIONALITY = 0.05;

	/**
	 * CLI:
	 *  --count=N   Quantidade de registros (padrão: 64 * nº de pipelines, mínimo 64)
	 */
	public function run(): void
	{
		$faker = fake('pt_BR');

		// Pré-checagens
		foreach ([DC::TABLE_DEALS, DC::TABLE_USERS, DC::TABLE_PIPELINES, DC::TABLE_STAGES] as $tbl) {
			if (!Schema::hasTable($tbl)) {
				$this->command?->warn("DealSeeder: tabela ausente: {$tbl}; abortando.");
				return;
			}
		}

		// Mapeia estágios por pipeline
		$stagesByPipeline = Stg::query()
			->select(['id', 'pipeline_id'])
			->get()
			->groupBy('pipeline_id')
			->map(fn($g) => $g->pluck('id')->all())
			->toArray();

		// Only use pipelines that have at least one stage (avoids empty stagePool → continue loop)
		$pipelineIds = array_keys($stagesByPipeline);
		if (!$pipelineIds) {
			Log::notice('DealSeeder: nenhum pipeline com stages; abortando.');
			return;
		}

		$users = Usr::query()->pluck('id')->all();
		if (!$users) {
			Log::notice('DealSeeder: não há usuários; abortando.');
			return;
		}

		$labels   = Lbl::query()->pluck('id')->all();
		$sources  = Src::query()->pluck('id')->all();
		$products = Psv::query()->pluck('id')->all();

		$baseN        = max(1, count($pipelineIds));
		$defaultCount = max(64, 64 * $baseN);
		$count        = $defaultCount;

		if ($this->command instanceof Command && $this->command->hasOption('count')) {
			try {
				$raw = $this->command->option('count');
				if (is_numeric($raw) && (int) $raw > 0) {
					$count = (int) $raw;
				} else {
					$this->command?->warn(sprintf(
						'DealSeeder: valor inválido para --count (%s); usando %d.',
						(string) $raw,
						$defaultCount
					));
				}
			} catch (\Throwable) {
				$this->command?->warn('DealSeeder: falha ao ler --count; usando valor padrão.');
			}
		}
		// HARD_CAP: limit iterations for dev/test speed (raised from 2; was 0 deals due to stageless pipelines)
		$HARD_CAP = 12;
		$count = min($HARD_CAP, $count); // original default: max(64, 64 * nPipelines)

		$systemUserId = $this->ensureSystemUser();

		$randBool = fn(int $pct) => fake()->boolean(max(0, min(100, $pct)));
		$maybe    = function (?callable $producer = null) use ($randBool) {
			return $randBool((int) round(self::OPTIONALITY * 100)) ? null : ($producer ? $producer() : null);
		};
		$pickCsv = function (array $pool, int $min, int $max): ?string {
			if (!$pool) return null;
			$n = random_int($min, $max);
			$slice = collect($pool)->shuffle()->take($n)->all();
			return $slice ? implode(',', $slice) : null;
		};

		$permHierarchy = ['client', 'user', 'accountant', 'admin', 'superAdmin'];
		$permResources = ['tasks', 'files', 'sources', 'products', 'contacts', 'invoices', 'members', 'custom-fields'];
		$permActions   = ['view', 'create', 'update', 'delete'];

		DB::transaction(function () use (
			$faker,
			$count,
			$pipelineIds,
			$stagesByPipeline,
			$users,
			$labels,
			$sources,
			$products,
			$systemUserId,
			$maybe,
			$pickCsv,
			$permHierarchy,
			$permResources,
			$permActions
		): void {
			$statusKeys = array_keys(Dl::$statuses);
			$evalValues = array_map(fn($c) => $c->value, EvaluationStatus::cases());

			for ($i = 0; $i < $count; $i++) {
				try {
					$nm = $faker->sentence(3);
					// (new \Symfony\Component\Console\Output\ConsoleOutput
					// )->writeln("Criando Acordo de Negócios: {$nm}");
					$pipelineId = $pipelineIds[array_rand($pipelineIds)];
					$stagePool  = $stagesByPipeline[$pipelineId] ?? [];
					if (!$stagePool) {
						// Sem estágio para o pipeline: pula registro para manter integridade
						continue;
					}

					$now = Carbon::now()->subDays($faker->numberBetween(0, 120))->subMinutes($faker->numberBetween(0, 1440));

					do $dealId = Str::uuid()->toString();
					while (Dl::where('id', $dealId)->exists());

					$responsible = $users[array_rand($users)];
					$supervisor  = $users[array_rand($users)];
					$customer    = $faker->boolean(60) ? $faker->company() : $faker->name();

					// involded: JSON com contatos normalizados (e-mail/telefone/contato)
					$invCount  = $faker->numberBetween(1, 5);
					$involded  = [];
					for ($j = 0; $j < $invCount; $j++) {
						$involded[] = [
							'name'    => $faker->name(),
							'email'   => $faker->safeEmail(),
							'phone'   => $faker->e164PhoneNumber(),
							'contact' => $faker->boolean(50) ? $faker->safeEmail() : $faker->e164PhoneNumber(),
							'role'    => $faker->randomElement(['buyer', 'influencer', 'decision_maker', 'technical']),
						];
					}

					// Permissões como string (JSON simples) — campo é TEXT
					$abilities = [];
					foreach ($permResources as $res) {
						foreach ($permActions as $act) {
							$abilities[] = "{$act}:{$res}";
						}
					}
					$role = $faker->randomElement($permHierarchy);
					$permPayload = [
						'role'       => $role,
						'granted'    => $abilities,
						'created_by' => $responsible,
					];
					$permissionsStr = json_encode($permPayload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

					DB::table(DC::TABLE_DEALS)->insert([
						'id'                     => $dealId,
						'name'                   => $nm,
						'phone'                  => $faker->e164PhoneNumber(),
						'email'                  => $maybe(fn() => $faker->safeEmail()),

						'price'                  => $faker->randomFloat(2, 300, 25000),

						// Pipeline + Stage coerentes
						'pipeline_id'            => $pipelineId,
						PJC::COL_STG_ID          => $stagePool[array_rand($stagePool)],

						// Grupo numérico simples
						PJC::COL_GRP_ID          => $faker->numberBetween(1, 8),

						// Campos textuais (CSV)
						'sources'                => $pickCsv($sources, 1, 3) ?? $faker->words(2, true),
						'products'               => $pickCsv($products, 1, 3) ?? $faker->words(2, true),
						'description'            => $faker->paragraphs($faker->numberBetween(1, 2), true),
						'customer'               => $customer,
						'notes'                  => $faker->sentences($faker->numberBetween(1, 3), true),
						'labels'                 => $pickCsv($labels, 1, 3) ?? null,
						'permissions'            => $permissionsStr,

						// Status do funil legada e status de avaliação (enum)
						'status'                 => $faker->randomElement($statusKeys),
						BC::COL_STT_LB           => $faker->randomElement($evalValues),

						'order'                  => $i,

						// Responsáveis
						'responsible'            => $responsible,
						'supervisor'             => $supervisor,

						// JSON
						'involded'               => json_encode($involded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),

						// Ativo (inteiro)
						AC::COL_IA               => $faker->boolean(90) ? 1 : 0,

						// Auditoria
						DC::COL_TABLE_CREATOR    => $systemUserId,
						DC::COL_TABLE_UPDATER    => null,
						'created_at'             => $now->toDateTimeString(),
						'updated_at'             => $now->addMinutes($faker->numberBetween(1, 60))->toDateTimeString(),
					]);
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
