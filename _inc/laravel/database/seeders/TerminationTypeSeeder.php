<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\TerminationType;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class TerminationTypeSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			$types = [
				'Rescisão sem justa causa',
				'Pedido de demissão',
				'Rescisão por justa causa',
				'Término de contrato (prazo determinado)',
				'Acordo mútuo',
				'Aposentadoria',
				'Falecimento',
				'Encerramento de filial/empresa',
				'Fim de projeto',
				'Não efetivado após experiência',
			];

			// Evita duplicidades acidentais na lista
			$types = array_values(array_unique(array_map('trim', $types)));

			foreach ($types as $name) {
				if ($name === '') {
					continue;
				}

				// Idempotente: cria se não existir; atualiza timestamps se já existir
				$model = TerminationType::firstOrNew(['name' => $name]);

				if (!$model->exists) {
					// created_by é guarded; definir por atribuição direta antes do save()
					$model->{DC::COL_TABLE_CREATOR} = $systemUserId;
				}

				$model->save();
			}
		}, 3);
	}
}
