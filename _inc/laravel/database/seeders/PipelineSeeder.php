<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ActivitiesConstants as AC;
use App\Config\Constants\ProjectsConstants as PJC;

use App\Models\Pipeline as Pln;

final class PipelineSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			$names = [
				'Vendas',
				'Onboarding',
				'Projetos',
				'Suporte',
				'Renovação',
				'Feedback',
				'Retenção',
				'Upsell',
				'Cross-sell',
				'Encerramento',
				'Pós-venda',
				'Implementação',
				'Treinamento',
				'Consultoria',
				'Manutenção',
				'Desenvolvimento',
				'Teste',
				'Deploy',
				'Monitoramento',
				'Análise de Requisitos',
				'Design',
				'Documentação',
				'Treinamento Interno',
				'Planejamento Estratégico',
				'Pesquisa de Mercado',
				'Gestão de Riscos',
				'Controle de Qualidade',
				'Suporte Técnico',
				'Atendimento ao Cliente',
				'Logística',
				'Financeiro',
				'Recursos Humanos',
				'Marketing',
				'Comunicação',
				'Jurídico',
				'Compras',
				'Administração',
				'TI',
				'Operações',
				'Desenvolvimento de Produto',
				'Inovação',
				'Parcerias Estratégicas',
				'Eventos Corporativos',
				'Relações Públicas',
				'Sustentabilidade',
				'Responsabilidade Social',
				'Governança Corporativa',
				'Segurança da Informação',
				'Análise de Dados',
				'Business Intelligence',
				'Transformação Digital'
			];

			$order = 0;

			foreach ($names as $nm) {
				try {
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("Criando pipeline: {$nm}");
					do $pipelineId = Str::uuid()->toString();
					while (Pln::where('id', $pipelineId)->exists());

					$p = new Pln();
					$p->id = $pipelineId;
					$p->{PJC::COL_PPL_NM} = $nm;
					$p->{AC::COL_OD}      = $order++;
					$p->{DC::COL_TABLE_CREATOR} = $systemUserId;
					$p->setAttribute(DC::COL_TABLE_UPDATER, null);

					$p->save();
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
