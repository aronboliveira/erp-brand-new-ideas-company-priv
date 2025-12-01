<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;

use App\Enums\MimeType as Mime;
use App\Enums\DocumentKind as Kind;

use App\Models\Document as Doc;
use App\Models\User as Usr;

final class DocumentSeeder extends Seeder
{
	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker) {
			$creatorId = DC::DEFAULT_UUID;

			$userIds = Usr::query()->pluck('id')->all();
			$pickIds = function (int $min, int $max) use ($userIds): array {
				if (!$userIds) return [];
				$n = random_int($min, $max);
				return $n > 0 ? collect($userIds)->shuffle()->take($n)->values()->all() : [];
			};

			$permPresets = [
				'7777777',
				'7755444',
				'7644444',
				'7544444',
				'7444444',
				'7644321',
				'7543321',
				'7400000',
				'7000000',
				'6644444',
				'6444440',
			];

			$samples = [
				['Contrato de Prestação de Serviços',  'pdf'],
				['Política de Privacidade',            'pdf'],
				['Relatório Financeiro - Abril',       'xlsx'],
				['Apresentação Comercial 2026',        'pptx'],
				['Manual do Usuário',                  'docx'],
				['Exportação de Clientes',             'csv'],
				['Script de Ajuste de Índices',        'sql'],
				['Backup Banco de Dados',              'sqlite'],
				['Changelog',                          'md'],
				['Notas de Reunião 2025-02-10',        'txt'],
				['Logo Horizontal',                    'png'],
				['Render Externo',                     'jpg'],
				['Ícones Vetoriais',                   'svg'],
				['Site - Página Inicial',              'html'],
				['Estilos Principais',                 'css'],
				['Integração API - Exemplo',           'json'],
				['Configuração do Servidor',           'xml'],
				['Vídeo Institucional',                'mp4'],
				['Pacote de Assets',                   'zip'],
				['Script de Rotina',                   'php'],
			];

			foreach ($samples as $i => [$name, $ext]) {
				do $docId = Str::uuid()->toString();
				while (Doc::where('id', $docId)->exists());

				$slug     = Str::slug($name);
				$filePath = "/storage/docs/{$slug}." . strtolower($ext);

				$mime = Mime::fromExtension($ext);
				$kind = $mime ? Kind::fromMime($mime) : Kind::fromExtension($ext);

				$sizeBytes = match (strtolower($ext)) {
					'pdf'    => random_int(200_000, 5_000_000),
					'xlsx'   => random_int(80_000, 2_000_000),
					'pptx'   => random_int(200_000, 8_000_000),
					'docx'   => random_int(60_000, 2_000_000),
					'csv'    => random_int(10_000, 800_000),
					'sql'    => random_int(50_000, 4_000_000),
					'sqlite' => random_int(1_000_000, 50_000_000),
					'md'     => random_int(3_000, 60_000),
					'txt'    => random_int(2_000, 40_000),
					'png'    => random_int(40_000, 6_000_000),
					'jpg'    => random_int(40_000, 6_000_000),
					'svg'    => random_int(4_000, 400_000),
					'html'   => random_int(5_000, 200_000),
					'css'    => random_int(3_000, 120_000),
					'json'   => random_int(5_000, 500_000),
					'xml'    => random_int(5_000, 500_000),
					'mp4'    => random_int(5_000_000, 120_000_000),
					'zip'    => random_int(500_000, 80_000_000),
					'php'    => random_int(2_000, 100_000),
					default  => random_int(10_000, 5_000_000),
				};

				$rules = $permPresets[$i % count($permPresets)];
				$viewers   = $pickIds(2, 10);
				$editors   = $pickIds(0, 5);
				$executors = $pickIds(0, 3);

				$d = new Doc();
				$d->id                   = $docId;
				$d->name                 = $name;
				$d->{DC::COL_IR}          = $faker->boolean(35) ? 'true' : 'false';
				$d->{DC::COL_IPV}           = $faker->boolean(55);
				$d->{DC::COL_FL_PT}            = $filePath;
				$d->extension            = strtolower($ext);
				if ($mime) $d->{DC::COL_MM_TP} = $mime;
				if ($kind) $d->type      = $kind;
				$d->size                 = $sizeBytes;
				$d->description          = $faker->boolean(60) ? $faker->sentence(12) : null;
				$d->notes                = $faker->boolean(40) ? $faker->sentence(10) : null;
				$d->{DC::COL_EXP_DT}      = $faker->boolean(30) ? $faker->dateTimeBetween('+10 days', '+18 months') : null;
				$d->{DC::COL_LA}        = $faker->boolean(70) ? $faker->dateTimeBetween('-6 months', 'now') : null;
				$d->{DC::COL_PERM_RLS}     = $rules;
				$d->viewers              = $viewers;
				$d->editors              = $editors;
				$d->executors            = $executors;
				$d->{DC::COL_TABLE_CREATOR}  = $creatorId;
				$d->setAttribute(DC::COL_TABLE_UPDATER, null);
				$d->save();
			}
		}, 3);
	}
}
