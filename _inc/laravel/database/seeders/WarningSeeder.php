<?php

namespace Database\Seeders;

use App\Config\Constants\UsersConstants as UC;
use App\Config\Constants\DatabaseConstants as DC;
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
				'Orientação de melhoria'
			];

			for ($i = 0; $i < $targetCount; $i++) {
				// escolhe destinatário e emissor distintos
				$toId = $faker->randomElement($employeeIds);
				$byId = $faker->randomElement(array_values(array_diff($employeeIds, [$toId])));

				$date = now('America/Sao_Paulo')->subDays(random_int(0, 540))->format('Y-m-d');
				$subject = $faker->boolean(80) ? $faker->randomElement($subjects) : null;
				$description = $faker->boolean(70) ? $faker->sentences(random_int(1, 3), true) : null;

				// idempotência: evita duplicar o mesmo aviso "lógico"
				$exists = Warning::query()
					->where(UC::COL_EMP_ID, $toId)
					->where(UC::COL_WRN_TO, $toId)
					->where(UC::COL_WRN_BY, $byId)
					->whereDate(UC::COL_WRN_DATE, $date)
					->when($subject, fn($q) => $q->where('subject', $subject))
					->exists();

				if ($exists) {
					continue;
				}

				$w = new Warning();
				$w->{UC::COL_EMP_ID}   = $toId;      // funcionário "alvo" do registro
				$w->{UC::COL_WRN_TO}   = $toId;      // destinatário
				$w->{UC::COL_WRN_BY}   = $byId;      // emissor
				$w->{UC::COL_WRN_DATE} = $date;
				$w->subject            = $subject;
				$w->description        = $description;
				$w->{DC::TABLE_CREATOR} = $systemUserId; // auditoria sem depender de auth()

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
			}
		}, 3);
	}
}
