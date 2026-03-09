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
use Illuminate\Support\Facades\{DB, Log};
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
			$titles = [];

			// Technology (40 titles)
			$techPrefixes = ['Desenvolvedor(a)', 'Analista de', 'Engenheiro(a) de', 'Arquiteto(a) de', 'Especialista em', 'Consultor(a) de', 'Gerente de', 'Coordenador(a) de'];
			$techDomains = ['Sistemas', 'Software', 'Redes', 'Dados', 'Cloud', 'Segurança', 'QA', 'DevOps', 'Mobile', 'Frontend', 'Backend', 'Infraestrutura', 'Banco de Dados', 'Machine Learning', 'IA'];
			$techSpecs = ['PHP', 'Java', 'Python', 'React', 'Node.js', 'AWS', 'Azure', 'Kubernetes', 'Docker', 'Laravel', 'Angular', 'Vue.js'];

			foreach ($techPrefixes as $prefix) {
				foreach ($techDomains as $domain) {
					if (count($titles) < 40) {
						$spec = $techSpecs[array_rand($techSpecs)];
						$titles[] = "$prefix $domain ($spec)";
					}
				}
			}

			// Digital Creative (25 titles)
			$creativeRoles = ['Designer', 'Editor(a)', 'Produtor(a)', 'Gestor(a) de', 'Especialista em', 'Analista de', 'Coordenador(a) de'];
			$creativeFields = ['Mídias Sociais', 'Conteúdo', 'UX/UI', 'Marketing Digital', 'SEO', 'Tráfego Pago', 'E-commerce', 'Vídeo', 'Áudio', 'Motion Graphics', 'Branding', 'Fotografia', 'Ilustração'];

			foreach ($creativeRoles as $role) {
				foreach ($creativeFields as $field) {
					if (count($titles) < 65) {
						$titles[] = "$role $field";
					}
				}
			}

			// HR (20 titles)
			$hrPrefixes = ['Analista de', 'Coordenador(a) de', 'Gerente de', 'Especialista em', 'Consultor(a) de', 'Business Partner de'];
			$hrAreas = ['Recrutamento', 'Seleção', 'Treinamento', 'Desenvolvimento', 'Benefícios', 'Remuneração', 'Departamento Pessoal', 'Clima Organizacional', 'Onboarding', 'Compliance Trabalhista'];

			foreach ($hrPrefixes as $prefix) {
				foreach ($hrAreas as $area) {
					if (count($titles) < 85) {
						$titles[] = "$prefix $area";
					}
				}
			}

			// Business (25 titles)
			$businessRoles = ['Analista de', 'Consultor(a) de', 'Gerente de', 'Coordenador(a) de', 'Especialista em', 'Diretor(a) de'];
			$businessFields = ['Negócios', 'Projetos', 'Processos', 'Operações', 'Estratégia', 'Inovação', 'Transformação Digital', 'Planejamento', 'Gestão', 'Consultoria'];

			foreach ($businessRoles as $role) {
				foreach ($businessFields as $field) {
					if (count($titles) < 110) {
						$titles[] = "$role $field";
					}
				}
			}

			// Finance (20 titles)
			$financeRoles = ['Analista', 'Consultor(a)', 'Gerente', 'Coordenador(a)', 'Especialista', 'Auditor(a)', 'Controller', 'Tesoureiro(a)'];
			$financeAreas = ['Financeiro', 'Contábil', 'Orçamento', 'Custos', 'Investimentos', 'Crédito', 'Contas a Pagar', 'Contas a Receber', 'Controladoria', 'Tesouraria'];

			foreach ($financeRoles as $role) {
				foreach ($financeAreas as $area) {
					if (count($titles) < 130) {
						$titles[] = "$role $area";
					}
				}
			}

			// Sales (20 titles)
			$salesTitles = [
				'Executivo(a) de Vendas',
				'Representante Comercial',
				'Consultor(a) Comercial',
				'Vendedor(a) Externo',
				'Inside Sales',
				'Key Account Manager',
				'Sales Development Rep',
				'Account Executive',
				'Gerente Comercial',
				'Coordenador(a) de Vendas',
				'Analista de Vendas',
				'Especialista em Pré-vendas',
				'Head of Sales',
				'Sales Manager',
				'Business Development',
				'Customer Success Manager',
				'Representante Técnico de Vendas',
				'Vendedor(a) Varejo',
				'Vendedor(a) Atacado',
				'Especialista em Negociação'
			];

			foreach ($salesTitles as $title) {
				if (count($titles) < 150) {
					$titles[] = $title;
				}
			}

			// Administration (20 titles)
			$adminPrefixes = ['Assistente', 'Auxiliar', 'Analista', 'Coordenador(a)', 'Gerente', 'Secretário(a)', 'Recepcionista'];
			$adminAreas = ['Administrativo', 'Executivo', 'Office', 'Facilities', 'Suprimentos', 'Arquivo', 'Protocolo', 'Correspondências'];

			foreach ($adminPrefixes as $prefix) {
				foreach ($adminAreas as $area) {
					if (count($titles) < 170) {
						$titles[] = "$prefix $area";
					}
				}
			}

			// Clerks (15 titles)
			$clerkRoles = ['Escriturário(a)', 'Atendente', 'Operador(a)', 'Digitador(a)', 'Conferente', 'Almoxarife', 'Auxiliar de Escritório', 'Assistente Comercial', 'Balconista', 'Caixa', 'Faturista', 'Conferente de Carga', 'Controlador(a) de Entrada/Saída', 'Auxiliar de Logística', 'Operador(a) de Telemarketing'];

			foreach ($clerkRoles as $role) {
				if (count($titles) < 185) {
					$titles[] = $role;
				}
			}

			// Cleaning roles (15 titles)
			$cleaningTitles = [
				'Auxiliar de Limpeza',
				'Zelador(a)',
				'Faxineiro(a)',
				'Servente de Limpeza',
				'Limpador(a)',
				'Ajudante Geral',
				'Encarregado(a) de Limpeza',
				'Supervisor(a) de Limpeza',
				'Coordenador(a) de Serviços Gerais',
				'Técnico(a) em Conservação',
				'Auxiliar de Conservação',
				'Limpador(a) de Vidros',
				'Auxiliar de Zeladoria',
				'Porteiro(a)',
				'Jardineiro(a)'
			];

			foreach ($cleaningTitles as $title) {
				if (count($titles) < 200) {
					$titles[] = $title;
				}
			}

			// Healthcare (20 titles)
			$healthRoles = ['Médico(a)', 'Enfermeiro(a)', 'Técnico(a) de Enfermagem', 'Fisioterapeuta', 'Nutricionista', 'Farmacêutico(a)', 'Psicólogo(a)', 'Dentista', 'Biomédico(a)', 'Auxiliar de Saúde', 'Técnico(a) de Laboratório', 'Recepcionista de Consultório', 'Coordenador(a) de Saúde', 'Gerente de Unidade de Saúde', 'Atendente de Farmácia', 'Cuidador(a) de Idosos', 'Técnico(a) em Radiologia', 'Auxiliar de Consultório', 'Assistente Social', 'Terapeuta Ocupacional'];

			foreach ($healthRoles as $role) {
				if (count($titles) < 220) {
					$titles[] = $role;
				}
			}

			// Legal (20 titles)
			$legalPrefixes = ['Advogado(a)', 'Assistente', 'Analista', 'Consultor(a)', 'Especialista', 'Estagiário(a)'];
			$legalAreas = ['Trabalhista', 'Cível', 'Empresarial', 'Tributário', 'Contratual', 'Imobiliário', 'Propriedade Intelectual', 'Compliance', 'Societário', 'Consumerista'];

			foreach ($legalPrefixes as $prefix) {
				foreach ($legalAreas as $area) {
					if (count($titles) < 240) {
						$titles[] = "$prefix $area";
					}
				}
			}

			// Gastronomy (16 titles) - Fill remaining slots
			$gastronomyTitles = [
				'Chef de Cozinha',
				'Cozinheiro(a)',
				'Auxiliar de Cozinha',
				'Confeiteiro(a)',
				'Padeiro(a)',
				'Bartender',
				'Garçom/Garçonete',
				'Atendente de Restaurante',
				'Maître',
				'Supervisor(a) de Restaurante',
				'Gerente de Restaurante',
				'Barista',
				'Auxiliar de Bar',
				'Chapeiro(a)',
				'Sushiman',
				'Pizzaiolo(a)'
			];

			foreach ($gastronomyTitles as $title) {
				if (count($titles) < 256) {
					$titles[] = $title;
				}
			}

			// Add any remaining titles if we didn't reach 256
			$additionalTitles = [
				'Analista de Métodos e Processos',
				'Coordenador(a) de Operações Logísticas',
				'Especialista em Sustentabilidade',
				'Analista de Comércio Exterior',
				'Coordenador(a) de Facilities Management',
				'Analista de Pesquisa de Mercado',
				'Especialista em Logística Reversa',
				'Analista de Comunicação Corporativa',
				'Coordenador(a) de Eventos',
				'Analista de Riscos Corporativos'
			];

			foreach ($additionalTitles as $title) {
				if (count($titles) < 256) {
					$titles[] = $title;
				}
			}

			// Ensure we have exactly 256 titles
			while (count($titles) < 256) {
				$titles[] = 'Profissional Multidisciplinar ' . (count($titles) + 1);
			}

			// Trim array to exactly 256
			$titles = array_slice($titles, 0, 256);

			echo "Total de cargos: " . count($titles) . "\n";
			echo "Cargos únicos: " . count(array_unique($titles)) . "\n";

			// Display breakdown by category
			echo "\nDistribuição por categorias:\n";
			echo "- Tecnologia: 40\n";
			echo "- Digital Creative: 25\n";
			echo "- RH: 20\n";
			echo "- Business: 25\n";
			echo "- Finance: 20\n";
			echo "- Sales: 20\n";
			echo "- Administration: 20\n";
			echo "- Clerks: 15\n";
			echo "- Cleaning roles: 15\n";
			echo "- Healthcare: 20\n";
			echo "- Legal: 20\n";
			echo "- Gastronomy: 16\n";

			// Function to check if a number is a power of two using bitwise operations
			function isPowerOfTwo($n)
			{
				return ($n > 0) && (($n & ($n - 1)) == 0);
			}

			// Function to generate a unique job title
			function generateUniqueTitle($existingTitles, $faker)
			{
				$categories = [
					'Tecnologia' => [
						'prefixes' => [
							'Analista de',
							'Desenvolvedor(a)',
							'Engenheiro(a) de',
							'Especialista em',
							'Consultor(a) de',
							'Arquiteto(a) de',
							'Coordenador(a) de TI',
							'Gerente de'
						],
						'suffixes' => [
							'Sistemas',
							'Redes',
							'Dados',
							'Cloud',
							'Segurança',
							'Software',
							'QA',
							'DevOps',
							'Mobile',
							'Frontend',
							'Backend',
							'Full Stack',
							'Infraestrutura'
						]
					],
					'Digital' => [
						'prefixes' => [
							'Designer',
							'Produtor(a)',
							'Editor(a)',
							'Gestor(a) de',
							'Especialista em',
							'Coordenador(a) de',
							'Analista de'
						],
						'suffixes' => [
							'Mídias Sociais',
							'Conteúdo Digital',
							'UX/UI',
							'Marketing Digital',
							'SEO',
							'Tráfego Pago',
							'E-commerce',
							'Video',
							'Audio'
						]
					],
					'Negócios' => [
						'prefixes' => [
							'Analista de',
							'Consultor(a) de',
							'Gerente de',
							'Coordenador(a) de',
							'Especialista em',
							'Head de'
						],
						'suffixes' => [
							'Negócios',
							'Projetos',
							'Processos',
							'Operações',
							'Estratégia',
							'Inovação',
							'Transformação Digital'
						]
					]
				];

				do {
					$category = array_rand($categories);
					$prefix = $categories[$category]['prefixes'][array_rand($categories[$category]['prefixes'])];
					$suffix = $categories[$category]['suffixes'][array_rand($categories[$category]['suffixes'])];

					// Sometimes add seniority level
					$seniority = ['', ' Júnior', ' Pleno', ' Sênior', ' Especialista', ' Líder'][rand(0, 5)];

					$title = $prefix . ' ' . $suffix . $seniority;

					// Sometimes add technology/area specialization
					if (rand(0, 3) === 0) {
						$techs = [
							'PHP',
							'Java',
							'Python',
							'React',
							'Angular',
							'Vue.js',
							'Laravel',
							'Node.js',
							'AWS',
							'Azure',
							'Google Cloud',
							'Kubernetes',
							'Docker',
							'PostgreSQL',
							'MySQL',
							'MongoDB',
							'React Native',
							'Flutter'
						];
						$title .= ' (' . $techs[array_rand($techs)] . ')';
					}
				} while (in_array($title, $existingTitles));

				return $title;
			}

			// Use Laravel's Faker if available, otherwise create a simple fallback
			try {
				$faker = \Faker\Factory::create('pt_BR');
			} catch (Exception $e) {
				// Fallback if Faker is not available
				$faker = null;
			}

			// Ensure array has power-of-2 elements using bitwise check
			$count = count($titles);
			while (!isPowerOfTwo($count)) {
				$newTitle = generateUniqueTitle($titles, $faker);
				$titles[] = $newTitle;
				$count = count($titles);
			}

			// Final verification
			$finalCount = count($titles);
			$logResult = log($finalCount, 2);
			echo "Array has {$finalCount} elements (2^{$logResult}) which is a power of 2!";
			foreach ($titles as $baseTitle) {
				try {
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("Criando Anúncio: {$baseTitle}");
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
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
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
