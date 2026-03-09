<?php

namespace Database\Seeders;

use App\Config\Constants\{
	CompaniesConstants as CC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Models\{Complaint, Employee};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class ComplaintSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$employeeIds = Employee::query()->pluck('id')->all();
			if (count($employeeIds) < 2) {
				Log::notice('Not enough employees to seed complaints (need at least 2). Skipping.');
				return;
			}

			// quantidade ≈ 40% do quadro, mínimo 6 e máximo 60
			$target = min(60, max(6, (int) floor(count($employeeIds) * 0.40)));

			$titles = [
				'Conduta inadequada em reunião',
				'Atrasos frequentes',
				'Descumprimento de procedimento',
				'Uso inadequado de recursos',
				'Comunicação agressiva',
				'Postura antiética',
				'Falha de conformidade',
				'Interrupções constantes de colegas',
			];

			$reasons = [
				'Desrespeito às políticas internas',
				'Falta de pontualidade',
				'Não observou instruções de segurança',
				'Conflito interpessoal',
				'Uso indevido de canais oficiais',
				'Negligência em tarefa crítica',
				'Quebra de confidencialidade',
				'Comportamento não profissional',
			];

			for ($i = 0; $i < $target; $i++) {
				try {
					// escolhe pares distintos (de → contra)
					$againstId = $faker->randomElement($employeeIds);
					$fromId    = $faker->randomElement(array_values(array_diff($employeeIds, [$againstId])));

					$date       = now('America/Sao_Paulo')->subDays(random_int(0, 720))->format('Y-m-d');
					$title      = $faker->randomElement($titles);
					$reason     = $faker->boolean(85) ? $faker->randomElement($reasons) : null;
					$desc       = $faker->boolean(75) ? $faker->sentences(random_int(1, 3), true) : null;
					$notes      = $faker->boolean(35) ? $faker->sentence() : null;
					// (new \Symfony\Component\Console\Output\ConsoleOutput
					// )->writeln("Criando Queixa para funcionário {$againstId} de {$fromId} - Título: {$title}");
					// idempotência: evita duplicar “mesma” queixa no dia
					$exists = Complaint::query()
						->where(UC::COL_EMP_ID,  $againstId)     // foco do registro
						->where(CC::COL_CPT_AGST, $againstId)    // contra quem
						->where(CC::COL_CPT_FRM,  $fromId)       // quem apresentou
						->whereDate(CC::COL_CPT_DT, $date)
						->where('title', $title)
						->exists();

					if ($exists) {
						continue;
					}

					$c = new Complaint();
					// definimos o “employee_id” como o alvo da queixa (consistente com o relacionamento principal)
					$c->{UC::COL_EMP_ID}   = $againstId;
					$c->{CC::COL_CPT_AGST} = $againstId;
					$c->{CC::COL_CPT_FRM}  = $fromId;
					$c->{CC::COL_CPT_DT}   = $date;
					$c->title              = $title;
					$c->reason             = $reason;
					$c->description        = $desc;
					$c->notes              = $notes;
					$c->{DC::COL_TABLE_CREATOR} = $systemUserId;

					try {
						$c->save();
					} catch (\Throwable $e) {
						Log::warning('Failed to seed complaint', [
							'against' => $againstId,
							'from'    => $fromId,
							'date'    => $date,
							'error'   => $e->getMessage(),
						]);
					}
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
