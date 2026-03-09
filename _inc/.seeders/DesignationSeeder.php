<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\{Arr, Str};
use App\Config\Constants\{
	DatabaseConstants as DC,
	CompaniesConstants as CPC,
	UsersConstants as UC
};
use App\Models\Designation as Dsg;
use App\Models\Department as Dpt;

final class DesignationSeeder extends Seeder
{
	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$creatorId = DC::DEFAULT_UUID;
			$deptIds = Dpt::query()->pluck('id')->all();
			if (!$deptIds) {
				Log::notice('No departments found. Skipping designation seeding.');
				return;
			}
			$count = 128;
			$designationsPool = [
				"Analista de Sistemas",
				"Engenheiro de Software",
				"Gerente de Projetos",
				"Desenvolvedor Full Stack",
				"Especialista em Segurança da Informação",
				"Consultor de TI",
				"Administrador de Banco de Dados",
				"Arquiteto de Soluções",
				"Coordenador de Infraestrutura",
				"Analista de Redes",
				"Desenvolvedor Mobile",
				"Engenheiro de Dados",
				"Gerente de TI",
				"Analista de Suporte Técnico",
				"Desenvolvedor Front-end",
				"Desenvolvedor Back-end",
				"Especialista em Cloud Computing",
				"Analista de Testes",
				"Engenheiro de DevOps",
				"Consultor de Segurança Cibernética",
				"Administrador de Sistemas",
				"Analista de Business Intelligence",
				"Desenvolvedor de Jogos",
				"Gerente de Desenvolvimento de Software",
				"Especialista em UX/UI",
				"Analista de Dados",
				"Engenheiro de Redes",
				"Coordenador de Projetos de TI",
				"Desenvolvedor de Aplicações Web",
				"Consultor de Transformação Digital",
				"Administrador de Redes",
				"Analista de Infraestrutura",
				"Engenheiro de Software Embarcado",
				"Gerente de Segurança da Informação",
				"Desenvolvedor de Inteligência Artificial",
				"Especialista em Big Data",
				"Analista de Sistemas ERP",
				"Engenheiro de Machine Learning",
				"Consultor de Cloud",
				"Administrador de Segurança",
				"Analista de Projetos de TI",
				"Desenvolvedor de Software Ágil",
				"Gerente de Operações de TI",
				"Especialista em Redes",
				"Analista de Suporte de Aplicações",
				"Engenheiro de Software Sênior",
				"Consultor de Infraestrutura",
				"Administrador de Dados",
				"Analista de Desenvolvimento de Software",
				"Desenvolvedor de Sistemas",
				"Gerente de Tecnologia",
				"Especialista em Virtualização",
				"Analista de Segurança da Informação",
				"Engenheiro de Software Júnior",
				"Consultor de TI Sênior",
				"Administrador de Sistemas Linux",
				"Analista de Redes e Telecomunicações",
				"Desenvolvedor de Software Pleno",
				"Gerente de Projetos Ágeis",
				"Especialista em Banco de Dados",
				"Analista de Suporte de TI",
				"Engenheiro de Software Full Stack",
				"Consultor de Segurança da Informação",
				"Administrador de Sistemas Windows",
				"Analista de Infraestrutura de TI",
				"Desenvolvedor de Aplicativos Móveis",
				"Engenheiro de Site Reliability",
				"Gerente de Desenvolvimento Ágil",
				"Especialista em Redes e Segurança",
				"Analista de Dados Sênior",
				"Engenheiro de Software de Nuvem",
				"Consultor de Transformação Ágil",
				"Administrador de Redes e Sistemas",
				"Analista de Projetos Ágeis",
				"Gerente de Recursos Humanos",
				"Coordenador de Marketing",
				"Analista Financeiro",
				"Assistente Administrativo",
				"Gerente de Vendas",
				"Especialista em Logística",
				"Analista de Compras",
				"Coordenador de Produção",
				"Gerente de Qualidade",
				"Analista de Comunicação",
				"Especialista em Relações Públicas",
				"Coordenador de Desenvolvimento de Negócios",
				"Gerente de Planejamento Estratégico",
				"Analista de Segurança da Informação",
				"Coordenador de Gestão de Projetos",
				"Especialista em Suporte Técnico",
				"Gerente Administrativo",
				"Analista Contábil",
				"Coordenador de Desenvolvimento de Produto",
				"Engenheiro de Produção",
				"Designer Gráfico",
				"Coordenador de Eventos",
				"Especialista em Treinamento e Desenvolvimento",
				"Analista de Saúde e Segurança Ocupacional",
				"Coordenador de Sustentabilidade",
				"Especialista em Inovação",
				"Analista de Riscos e Conformidade",
				"Faxineiro",
				"Jardineiro",
				"Segurança",
				"Recepcionista",
				"Motorista",
				"Auxiliar de Serviços Gerais",
				"Mensageiro",
				"Operador de Máquinas",
				"Estoquista",
				"Auxiliar de Cozinha",
				"Garçom",
				"Auxiliar de Produção",
				"Gerente de Controladoria",
				"Analista de Relações Trabalhistas",
				"Coordenador de Benefícios",
				"Especialista em Desenvolvimento Organizacional",
				"Analista de Recrutamento e Seleção",
				"Coordenador de Treinamento",
				"Gerente de Comunicação Interna",
				"Analista de Mídias Sociais",
				"Coordenador de SEO",
				"Especialista em Marketing Digital",
				"Analista de E-commerce",
				"Coordenador de Vendas Externas",
				"Gerente de Contas",
				"Analista de Planejamento de Vendas",
				"Coordenador de Logística Reversa",
				"Especialista em Cadeia de Suprimentos",
				"Analista de Compras Estratégicas",
				"Coordenador de Produção Industrial",
				"Gerente de Controle de Qualidade",
				"Analista de Comunicação Corporativa",
				"Coordenador de Relações com a Mídia",
				"Especialista em Desenvolvimento de Negócios Internacionais",
				"Analista de Planejamento Estratégico",
				"Coordenador de Segurança da Informação",
				"Especialista em Suporte ao Cliente",
				"Diretor de Tecnologia da Informação",
				"Diretor de Operações",
				"Diretor Financeiro",
				"Diretor de Recursos Humanos",
				"Diretor de Marketing",
				"Diretor Comercial",
				"Social Media",
				"Content Creator",
				"UX Researcher",
				"Especialista em Aprendizado de Máquina",
				"Engenheiro de Robótica",
			];
			$pool = array_values(array_unique($designationsPool));
			$expected = max($count, count($pool));
			while (count($pool) < $expected)
				$pool[] = Arr::random($pool);
			for ($i = 0; $i < count($pool); $i++) {
				try {
					do $designationId = Str::uuid()->toString();
					while (Dsg::where('id', $designationId)->exists());
					$deptId = $faker->randomElement($deptIds);
					$name = $pool[$i]; // * se houver índice único futuro, considerar compor com o departamento
					$budget = $faker->boolean(60)
						? $faker->randomFloat(2, 1_000, 250_000)
						: 0.00;
					$validFrom = $faker->dateTimeBetween('-2 years', 'now');
					$validTo   = $faker->dateTimeBetween('+1 years', '+9 years');
					(new \Symfony\Component\Console\Output\ConsoleOutput())->writeln("Seeding designation: {$name} ({$designationId})");
					$d = new Dsg();
					$d->id                      = $designationId;
					$d->{UC::COL_DSG_NM}        = $name;
					$d->{CPC::COL_DEP_ID}       = $deptId;
					$d->{CPC::COL_EBDG}         = $budget;
					$d->{CPC::COL_VFROM}        = $validFrom;
					$d->{CPC::COL_VTO}          = $validTo;
					$d->description             = $faker->boolean(55) ? $faker->sentence(12) : null;
					$d->notes                   = $faker->boolean(35) ? $faker->sentence(10) : null;
					$d->{DC::COL_TABLE_CREATOR}     = $creatorId;
					$d->setAttribute(DC::COL_TABLE_UPDATER, null);
					$d->save();
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
