<?php

namespace Database\Seeders;

use App\Config\Constants\{
	CompaniesConstants as CPC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Models\{Designation, Employee, Promotion};
use App\Traits\EnsuresSystemUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class PromotionSeeder extends Seeder
{
	use EnsuresSystemUser;

	private const MAX_PROMOTIONS_PER_EMPLOYEE = 4;
	private const PAST_DAYS  = 540; // datas no passado até ~18 meses
	private const FUTURE_DAYS = 30; // e até +30 dias no futuro

	/** Títulos “facade/alias” (UC::COL_PRMT_TL), não necessariamente o cargo real. */
	private const TITLES = [
		'Júnior',
		'Pleno',
		'Sênior',
		'Líder',
		'Coordenador(a)',
		'Especialista',
		'Tech Lead',
		'Analista Sênior',
		'Supervisor(a)',
	];

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			// Funcionários ativos
			$employees = Employee::query()
				->where(UC::COL_IA, 1)
				->select(['id', CPC::COL_DEP_ID])
				->get();

			if ($employees->isEmpty()) {
				Log::notice('PromotionSeeder: nenhum funcionário ativo encontrado. Abortando.');
				return;
			}

			// Índice rápido de designações por departamento
			$designationsByDept = Designation::query()
				->select(['id', CPC::COL_DEP_ID])
				->get()
				->groupBy(CPC::COL_DEP_ID);

			// Coleção de fallback com TODAS as designações
			$allDesignations = Designation::query()->pluck('id')->all();
			if (empty($allDesignations)) {
				Log::notice('PromotionSeeder: nenhuma designação encontrada. Abortando.');
				return;
			}

			$HARD_CAP = 2; // Hard cap to prevent excessive record creation
			$promoCreated = 0;
			foreach ($employees as $emp) {
				if ($promoCreated >= $HARD_CAP) break;
				$qty = random_int(0, self::MAX_PROMOTIONS_PER_EMPLOYEE);
				if ($qty === 0) {
					continue;
				}

				for ($i = 0; $i < $qty; $i++) {
					if ($promoCreated >= $HARD_CAP) break 2; // Hard cap guard
					try {
						// (new \Symfony\Component\Console\Output\ConsoleOutput
						// )->writeln("Criando Promoção para funcionário ID: {$emp->id}");
						$now = now('America/Sao_Paulo');
						// data entre (-PAST_DAYS .. +FUTURE_DAYS)
						$start = $now
							->subDays(random_int(0, self::PAST_DAYS))
							->addDays(random_int(0, self::FUTURE_DAYS));

						// Escolhe uma designação do mesmo departamento, se existir; senão, qualquer uma
						$deptId = $emp->{CPC::COL_DEP_ID};
						$pool   = $designationsByDept->get($deptId)?->pluck('id')->all() ?? [];
						if (empty($pool)) {
							$pool = $allDesignations;
						}
						$designationId = $faker->randomElement($pool);

						$title = $faker->randomElement(self::TITLES);

						// Evita duplicar a mesma combinação (emp+desig+data+título)
						$exists = Promotion::query()
							->where(UC::COL_EMP_ID, $emp->id)
							->where(UC::COL_DSG_ID, $designationId)
							->where(UC::COL_PRMT_DT, $start->format('Y-m-d'))
							->where(UC::COL_PRMT_TL, $title)
							->exists();

						if ($exists) {
							continue;
						}

						$p = new Promotion();
						$p->{UC::COL_EMP_ID}  = $emp->id;
						$p->{UC::COL_DSG_ID}  = $designationId;
						$p->{UC::COL_PRMT_TL} = $title;
						$p->{UC::COL_PRMT_DT} = $start->format('Y-m-d');
						$p->description       = $faker->boolean(55) ? $faker->sentence(12) : null;

						// Auditoria
						$p->{DC::COL_TABLE_CREATOR} = $systemUserId;

						try {
							$p->save();
							$promoCreated++;
						} catch (\Throwable $e) {
							Log::warning('PromotionSeeder: falha ao salvar promoção', [
								'employee_id'   => $emp->id,
								'designation_id' => $designationId,
								'promotion_date' => $p->{UC::COL_PRMT_DT},
								'title'         => $title,
								'error'         => $e->getMessage(),
							]);
						}
					} catch (\Exception $e) {
						Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
						continue;
					}
				}
			}
		}, 3);
	}
}
