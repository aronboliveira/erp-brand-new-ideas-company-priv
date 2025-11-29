<?php

namespace Database\Seeders;

use App\Config\Constants\{
	CompaniesConstants as CC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC
};
use App\Models\{
	Branch,
	Department,
	Employee,
	Meeting
};
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class MeetingSeeder extends Seeder
{
	// Ajuste aqui o volume padrão de registros
	private const TOTAL_MEETINGS = 40;

	public function run(): void
	{
		DB::transaction(function (): void {
			$tz    = 'America/Sao_Paulo';
			$today = CarbonImmutable::now($tz)->startOfDay();
			$faker = \Faker\Factory::create('pt_BR');

			// Cache leve de relacionamentos opcionais
			$employees   = Employee::query()->select(['id'])->get();
			$branches    = Branch::query()->select(['id'])->get();
			$departments = Department::query()->select(['id'])->get();

			if ($employees->isEmpty()) {
				Log::info('MeetingSeeder: nenhum Employee encontrado — seguirei gerando reuniões sem vínculo de funcionário.');
			}

			// Geração
			$created = 0;
			$updated = 0;

			for ($i = 0; $i < self::TOTAL_MEETINGS; $i++) {
				// Data até 30 dias à frente
				$startDate = $today->addDays(random_int(0, 30));

				// Horário entre 09:00 e 18:00
				$hour   = random_int(9, 17);
				$minute = [0, 15, 30, 45][random_int(0, 3)];
				$time   = sprintf('%02d:%02d:00', $hour, $minute);

				// Durações coerentes com o Model
				$min = random_int(15, 45);           // 15 a 45
				$exp = max($min, $min + [0, 15, 30][random_int(0, 2)]);
				$max = max($exp, $exp + [0, 15, 30, 45, 60][random_int(0, 4)]);
				// clamp (mesmos limites do Model: 8h/12h)
				$min = max(1, min($min, 8 * 60));
				$exp = max($min, min($exp, 8 * 60));
				$max = max($exp, min($max, 12 * 60));

				$titlePool = [
					'Alinhamento de Projeto',
					'Revisão de Backlog',
					'Reunião com Cliente',
					'Planejamento de Sprint',
					'Retrospectiva',
					'Kickoff de Iniciativa',
					'Acompanhamento de Implantação',
				];
				$title = $faker->randomElement($titlePool);

				// Relacionamentos opcionais
				$employeeId   = $employees->isNotEmpty()   ? $employees->random()->id : null;
				$branchId     = $branches->isNotEmpty()    ? $branches->random()->id : null;
				$departmentId = $departments->isNotEmpty() ? $departments->random()->id : null;

				// URL e metadata
				$url = 'https://meet.example.com/' . Str::lower(Str::random(10));

				$attachments = [
					['type' => 'agenda', 'filename' => 'agenda.pdf', 'required' => false],
				];

				$invited = [
					$faker->safeEmail(),
					$faker->optional(0.6)->safeEmail(),
				];
				$invited = array_values(array_filter($invited));

				$conditions = [
					'gravacao_autorizada' => $faker->boolean(80),
					'externo'             => $faker->boolean(30),
				];

				$reminders = [
					['channel' => 'email', 'offset_minutes' => 60],
					['channel' => 'popup', 'offset_minutes' => 10],
				];

				$tags = $faker->randomElements(
					['interno', 'cliente', 'prioridade', 'remoto', 'presencial'],
					random_int(1, 3)
				);

				// Chave lógica para idempotência
				$key = [
					'employee_id'   => $employeeId,
					'date'          => $startDate->toDateString(),
					'time'          => $time,
					'title'         => $title,
				];

				$payload = [
					// deixar 'code' null para o Model gerar UUID único no saving()
					CC::COL_BRC_ID          => $branchId,
					CC::COL_DEP_ID          => $departmentId,
					PJC::COL_MIN_DR         => $min,
					PJC::COL_EXP_DR         => $exp,
					PJC::COL_MAX_DR         => $max,
					'url'                   => $url,
					'note'                  => $faker->optional(0.5)->sentence(8),
					'attachments'           => $attachments,
					'invited'               => $invited,
					'conditions'            => $conditions,
					'reminders'             => $reminders,
					'tags'                  => $tags,
				];

				$existing = Meeting::query()->where($key)->first();
				if ($existing) {
					$existing->fill($payload)->save();
					$updated++;
				} else {
					Meeting::create($key + $payload);
					$created++;
				}
			}

			Log::info("MeetingSeeder finalizado", ['created' => $created, 'updated' => $updated]);
		});
	}
}
