<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\TerminationType;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};

final class TerminationTypeSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();
			$types = [
				'Rescisão sem justa causa',
				'Rescisão por justa causa',
				'Término de contrato (prazo determinado)',
				'Acordo mútuo',
				'Aposentadoria',
				'Falecimento',
				'Encerramento de filial/empresa',
				'Fim de projeto',
				'Não efetivado após experiência',
				'Mudança de cidade/estado/país',
				'Conflito interpessoal',
				'Redução de quadro de funcionários',
				'Motivos de saúde',
				'Retorno aos estudos',
				'Motivos familiares',
				'Desempenho insatisfatório',
				'Comportamento inadequado',
				'Denúncia',
				'Inadimplência',
				'Insubordinação',
				'Abandono de emprego',
				'Faltas ou atrasos excessivos',
				'Dano ao patrimônio',
				'Danos morais',
				'Assédio',
				'Vazamento de informações confidenciais',
				'Violação de políticas internas',
				'Outros motivos disciplinares',
				'Outros',
			];

			// Evita duplicidades acidentais na lista
			$types = array_values(array_unique(array_map('trim', $types)));

			foreach ($types as $name) {
				try {
					if ($name === '') {
						continue;
					}
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("Criando Tipo de Demissão: {$name}");
					// Idempotente: cria se não existir; atualiza timestamps se já existir
					$model = TerminationType::firstOrNew(['name' => $name]);

					if (!$model->exists) {
						// created_by é guarded; definir por atribuição direta antes do save()
						$model->{DC::COL_TABLE_CREATOR} = $systemUserId;
					}

					$model->save();
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
