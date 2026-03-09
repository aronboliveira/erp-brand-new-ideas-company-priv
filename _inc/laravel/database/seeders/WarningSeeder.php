<?php

namespace Database\Seeders;

use App\Config\Constants\{CompaniesConstants as CC, DatabaseConstants as DC, UsersConstants as UC};
use App\Models\{Employee, Warning};
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class WarningSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$systemUserId = $this->ensureSystemUser();

			$employeeIds = Employee::query()->pluck('id')->all();
			if (count($employeeIds) < 2) {
				Log::notice('Not enough employees to seed warnings (need at least 2). Skipping.');
				return;
			}

			// quantidade ≈ 35% do quadro, mínimo 5 e máximo 50
			$targetCount = min(50, max(5, (int) floor(count($employeeIds) * 0.35)));

			$subjects = [
				'Atraso recorrente',
				'Conduta inadequada',
				'Descumprimento de política interna',
				'Ausência não justificada',
				'Uso indevido de recursos',
				'Procedimento de segurança',
				'Advertência formal',
				'Orientação de melhoria',
				'Falta de cumprimento de prazos',
				'Comportamento não profissional',
				'Uso de linguagem inadequada',
				'Desrespeito a colegas de trabalho',
				'Violação de normas de segurança',
				'Negligência nas responsabilidades',
				'Falta de pontualidade',
				'Assédio moral',
				'Desobediência a superiores',
				'Uso inadequado de equipamentos',
				'Falta de colaboração em equipe',
				'Divulgação de informações confidenciais',
				'Violação de políticas de privacidade',
				'Uso indevido de recursos da empresa',
				'Comportamento antiético',
				'Falta de respeito às normas internas',
				'Desrespeito ao código de conduta',
				'Violação de políticas de segurança',
				'Uso de linguagem inadequada',
				'Falta de cumprimento de prazos',
				'Comportamento não profissional',
				'Negligência nas responsabilidades',
				'Falta de pontualidade',
			];

			for ($i = 0; $i < $targetCount; $i++) {
				try {
					// escolhe destinatário e emissor distintos
					$toId = $faker->randomElement($employeeIds);
					$byId = $faker->randomElement(array_values(array_diff($employeeIds, [$toId])));

					$date = now('America/Sao_Paulo')->subDays(random_int(0, 540))->format('Y-m-d');
					$subject = $faker->boolean(80) ? $faker->randomElement($subjects) : null;
					$description = $faker->boolean(70) ? $faker->sentences(random_int(1, 3), true) : null;
					// (new \Symfony\Component\Console\Output\ConsoleOutput
					// )->writeln("Criando Aviso para funcionário {$toId} de {$byId} - Assunto: {$subject}");
					// idempotência: evita duplicar o mesmo aviso "lógico"
					$exists = Warning::query()
						->where(UC::COL_EMP_ID, $toId)
						->where(CC::COL_WRN_TO, $toId)
						->where(CC::COL_WRN_BY, $byId)
						->whereDate(CC::COL_WRN_DATE, $date)
						->when($subject, fn($q) => $q->where('subject', $subject))
						->exists();

					if ($exists) {
						continue;
					}

					$w = new Warning();
					$w->{UC::COL_EMP_ID}   = $toId;      // funcionário "alvo" do registro
					$w->{CC::COL_WRN_TO}   = $toId;      // destinatário
					$w->{CC::COL_WRN_BY}   = $byId;      // emissor
					$w->{CC::COL_WRN_DATE} = $date;
					$w->subject            = $subject;
					$w->description        = $description;
					$w->{DC::COL_TABLE_CREATOR} = $systemUserId; // auditoria sem depender de auth()

					try {
						$w->save();
					} catch (\Throwable $e) {
						Log::warning('Failed to seed warning', [
							'to'    => $toId,
							'by'    => $byId,
							'date'  => $date,
							'error' => $e->getMessage(),
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
