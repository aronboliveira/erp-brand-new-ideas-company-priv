<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\AwardType;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class AwardTypeSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		$defaults = [
			'Funcionário do Mês',
			'Destaque de Desempenho',
			'Trabalho em Equipe',
			'Excelência em Atendimento',
			'Inovação',
			'Liderança',
			'Cumprimento de Metas',
			'Assiduidade',
			'Segurança do Trabalho',
			'Qualidade',
			'Atendimento ao Cliente',
			'Produtividade',
			'Mentoria / Treinador',
			'Projeto do Ano',
			'Equipe do Mês',
		];

		DB::transaction(function () use ($defaults) {
			$creatorId = $this->ensureSystemUser();

			foreach ($defaults as $name) {
				AwardType::query()->firstOrCreate(
					['name' => $name],
					[DC::TABLE_CREATOR => $creatorId]
				);
			}
		}, 3);
	}
}
