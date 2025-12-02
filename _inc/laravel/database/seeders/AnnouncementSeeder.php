<?php

namespace Database\Seeders;

use App\Config\Constants\{
	CompaniesConstants as CC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Models\{
	Announcement,
	Branch,
	Department,
	Employee
};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Carbon\Carbon;

class AnnouncementSeeder extends Seeder
{
	public function run(): void
	{
		DB::transaction(function (): void {
			$faker = Faker::create('pt_BR');

			// Coleções base
			$branchIds = Branch::query()->pluck('id')->all();
			if (empty($branchIds)) {
				$branch = Branch::query()->create([
					'id'   => (string) Str::uuid(),
					'name' => 'Matriz',
				]);
				$branchIds = [$branch->id];
			}

			$deptIds = Department::query()->pluck('id')->all();
			if (empty($deptIds)) {
				$dept = Department::query()->create([
					'id'        => (string) Str::uuid(),
					'name'      => 'Tecnologia',
					CC::COL_BRC_ID => $branchIds[0],
				]);
				$deptIds = [$dept->id];
			}

			$empIds = Employee::query()->pluck('id')->all(); // opcional

			// Catálogo de títulos
			$titles = [
				'Analista de Suporte N2',
				'Desenvolvedor(a) PHP/Laravel Pleno',
				'Administrador(a) de Sistemas Linux',
				'Engenheiro(a) de Redes',
				'Técnico(a) de Campo',
				'DevOps Engineer',
				'Product Owner',
				'UI/UX Designer',
				'Coordenador(a) de Help Desk',
				'Analista de Segurança da Informação',
				'DBA PostgreSQL',
				'Analista de Testes / QA',
			];

			foreach ($titles as $baseTitle) {
				$branchId = $this->pick($branchIds);
				$deptId   = $this->pick($deptIds);
				$employee = $this->pickOrNull($empIds, 0.7);
				$recruit  = $this->pickOrNull($empIds, 0.6);

				// Datas
				$start   = Carbon::today()->addDays(random_int(0, 10));
				$planned = (clone $start)->addDays(random_int(7, 30));
				$end     = random_int(0, 1) ? (clone $planned)->addDays(random_int(15, 60)) : null;

				// Steps / pipeline
				$steps = [
					[
						'name'     => 'Triagem de currículos',
						'target'   => $start->copy()->addDays(3)->toDateString(),
						'status'   => 'pending', // pending | doing | done
						'owner'    => $faker->firstName() . ' ' . $faker->lastName(),
					],
					[
						'name'     => 'Entrevista técnica',
						'target'   => $start->copy()->addDays(7)->toDateString(),
						'status'   => 'pending',
						'owner'    => $faker->firstName() . ' ' . $faker->lastName(),
					],
					[
						'name'     => 'Proposta',
						'target'   => $planned->copy()->subDays(2)->toDateString(),
						'status'   => 'pending',
						'owner'    => $faker->firstName() . ' ' . $faker->lastName(),
					],
				];

				// Requirements / tags
				$requirements = $faker->randomElements([
					'Experiência com Linux',
					'Conhecimentos em redes TCP/IP',
					'Git e CI/CD',
					'Atendimento ao usuário',
					'Inglês técnico',
					'Banco de dados SQL',
					'Noções de segurança (OWASP)',
					'Docker/Kubernetes',
					'PHP/Laravel',
					'Node/Next.js',
					'Python',
				], random_int(3, 6));

				$tags = $faker->randomElements([
					'remoto',
					'alocado',
					'híbrido',
					'pj',
					'clt',
					'pleno',
					'sênior',
					'junior',
					'24x7',
					'plantão',
				], random_int(3, 5));

				$title = $baseTitle . ' - ' . $faker->city();

				Announcement::updateOrCreate(
					// chave “quase” idempotente
					['title' => $title, CC::COL_BRC_ID => $branchId],
					[
						'id'                 => (string) Str::uuid(),
						PJC::COL_S_DT        => $start->toDateString(),
						PJC::COL_E_DT        => $end?->toDateString(),
						CC::COL_DEP_ID       => $deptId,
						UC::COL_EMP_ID       => $employee,
						'recruiter'          => $recruit,
						'description'        => $faker->paragraphs(2, true),
						UC::COL_IA           => true,
						UC::COL_IS_RD        => true,
						PJC::COL_PLN_ST      => $planned->toDateString(),
						'requirements'       => $requirements,
						'tags'               => $tags,
						'steps'              => $steps,
						DC::COL_TABLE_CREATOR    => null, // preencha se desejar atrelar usuário criador
					]
				);
			}
		});
	}

	private function pick(array $ids): mixed
	{
		return $ids[array_rand($ids)];
	}

	private function pickOrNull(array $ids, float $prob = 0.5): mixed
	{
		return (mt_rand() / mt_getrandmax()) < $prob && $ids
			? $this->pick($ids)
			: null;
	}
}
