<?php

namespace Database\Seeders;

use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Models\Source as Src;

final class SourceSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();
			$names = [
				'Portal Institucional',
				'CRM Corporativo',
				'Integração ERP',
				'Formulário Web',
				'Importação CSV',
				'API Externa',
				'Help Desk',
				'Campanha Marketing',
				'Indicação Cliente',
				'Evento Presencial',
				'Redes Sociais',
				'Feira Comercial',
				'Telemarketing',
				'Parceria Estratégica',
				'Pesquisa de Mercado',
				'Anúncio Online',
				'Blog Corporativo',
				'Vídeo Promocional',
				'Newsletter',
				'Webinar',
				'Podcast',
				'Aplicativo Móvel',
				'Loja Física',
				'Revendedor Autorizado',
				'Programa de Fidelidade',
				'Consultor Externo',
				'Outro'
			];
			while (log(count($names), 2) % 1 !== 0 || count($names) < 128)
				$names[] = fake()->words(2, true);
			foreach ($names as $nm) {
				try {
					(new \Symfony\Component\Console\Output\ConsoleOutput
					)->writeln("Criando Fonte de Projeto: {$nm}");
					do $sourceId = Str::uuid()->toString();
					while (Src::where('id', $sourceId)->exists());

					$s = new Src();
					$s->id = $sourceId;
					$s->name = $nm;
					$s->{DC::COL_TABLE_CREATOR} = $systemUserId;
					$s->{DC::COL_TABLE_UPDATER} = null;
					$s->save();
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
