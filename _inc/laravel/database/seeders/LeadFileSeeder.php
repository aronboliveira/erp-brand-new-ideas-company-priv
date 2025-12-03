<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\ProjectsConstants as PJC;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LeadFileSeeder extends Seeder
{
	private const CHUNK_SIZE   = 1000;
	private const PER_LEAD_MIN = 1;   // arquivos mínimos por lead
	private const PER_LEAD_MAX = 4;   // arquivos máximos por lead
	private const OPTIONAL_PCT = 65;  // probabilidade média p/ opcionais

	public function run(): void
	{
		// Tabelas essenciais
		foreach ([DC::TABLE_LD_FILES, DC::TABLE_LEADS] as $tbl) {
			if (!Schema::hasTable($tbl)) {
				$this->command?->warn("Tabela ausente: {$tbl}. Seeder abortado.");
				return;
			}
		}

		$leads = DB::table(DC::TABLE_LEADS)->select('id')->get();
		if ($leads->isEmpty()) {
			$this->command?->warn('Nenhum lead encontrado. Seeder abortado.');
			return;
		}

		// Usuários opcionais (para executors/editors/viewers e auditoria)
		$users = Schema::hasTable(DC::TABLE_USERS)
			? DB::table(DC::TABLE_USERS)->select('id')->get()
			: collect();

		$userIds = $users->pluck('id')->all();

		// Regra de quantidade: --count se disponível; caso contrário 64 * nº_de_leads
		$target = 64 * max(1, $leads->count());
		if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
			$opt = (int) $this->command->option('count');
			if ($opt > 0) $target = $opt;
		}

		// Catálogo (extensão => mime) por categoria (valores compatíveis com FileCategory)
		$catalog = [
			'document' => [
				'pdf'  => 'application/pdf',
				'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'odt'  => 'application/vnd.oasis.opendocument.text',
				'rtf'  => 'application/rtf',
				'txt'  => 'text/plain',
				'md'   => 'text/markdown',
			],
			'spreadsheet' => [
				'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'csv'  => 'text/csv',
				'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
			],
			'presentation' => [
				'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'odp'  => 'application/vnd.oasis.opendocument.presentation',
			],
			'image' => [
				'jpg'  => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'png'  => 'image/png',
				'gif'  => 'image/gif',
				'svg'  => 'image/svg+xml',
				'webp' => 'image/webp',
			],
			'audio' => [
				'mp3' => 'audio/mpeg',
				'wav' => 'audio/wav',
				'ogg' => 'audio/ogg',
			],
			'video' => [
				'mp4'  => 'video/mp4',
				'webm' => 'video/webm',
				'mov'  => 'video/quicktime',
			],
			'archive' => [
				'zip' => 'application/zip',
				'7z'  => 'application/x-7z-compressed',
				'rar' => 'application/x-rar-compressed',
				'tar' => 'application/x-tar',
				'gz'  => 'application/gzip',
			],
			'code' => [
				'php'  => 'text/x-php',
				'js'   => 'text/javascript',
				'py'   => 'text/x-python',
				'json' => 'application/json',
				'xml'  => 'application/xml',
				'css'  => 'text/css',
				'html' => 'text/html',
				'sql'  => 'application/sql',
				'sh'   => 'text/x-shellscript',
			],
			'text' => [
				'log' => 'text/plain',
				'txt' => 'text/plain',
			],
			'configuration' => [
				'ini'  => 'text/plain',
				'cfg'  => 'text/plain',
				'yaml' => 'text/yaml',
				'yml'  => 'text/yaml',
				'toml' => 'text/toml',
			],
			'binary' => [
				'bin' => 'application/octet-stream',
				'exe' => 'application/octet-stream',
				'dll' => 'application/octet-stream',
			],
			'font' => [
				'ttf'  => 'font/ttf',
				'woff' => 'font/woff',
				'woff2' => 'font/woff2',
				'otf'  => 'font/otf',
			],
			'database' => [
				'sqlite' => 'application/x-sqlite3',
				'db'     => 'application/x-sqlite3',
			],
			'certificate' => [
				'crt' => 'application/x-x509-ca-cert',
				'pem' => 'application/x-x509-ca-cert',
				'key' => 'application/pkcs8',
			],
		];

		$nameSeeds = [
			'proposal',
			'contract',
			'invoice',
			'logo',
			'screenshot',
			'dataset',
			'report',
			'notes',
			'backup',
			'config',
			'export',
			'timeline',
			'diagram',
			'wireframe',
			'statement',
			'pricing',
			'manual',
			'guide',
			'audit',
			'spec',
			'scope',
			'receipt',
		];

		$rows      = [];
		$inserted  = 0;
		$now       = Carbon::now();

		$json = fn($v) => $v === null ? null : json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
		$maybe = fn(callable $fn) => fake()->boolean(self::OPTIONAL_PCT) ? $fn() : null;

		foreach ($leads as $lead) {
			if ($target > 0 && $inserted >= $target) break;

			$filesForLead = fake()->numberBetween(self::PER_LEAD_MIN, self::PER_LEAD_MAX);
			if ($target > 0 && $inserted + $filesForLead > $target) {
				$filesForLead = max(0, $target - $inserted);
			}
			if ($filesForLead === 0) continue;

			for ($i = 0; $i < $filesForLead; $i++) {
				if ($target > 0 && $inserted >= $target) break 2;

				// Categoria, extensão e MIME coerentes
				$category = Arr::random(array_keys($catalog));
				$extMime  = $catalog[$category];
				$ext      = Arr::random(array_keys($extMime));
				$mime     = $extMime[$ext];

				// Tamanho plausível por categoria (bytes)
				$size = match ($category) {
					'video'       => fake()->numberBetween(2000000, 120000000),
					'audio'       => fake()->numberBetween(200000, 12000000),
					'image'       => fake()->numberBetween(40000, 8000000),
					'archive'     => fake()->numberBetween(500000, 80000000),
					'document'    => fake()->numberBetween(10000, 6000000),
					'spreadsheet' => fake()->numberBetween(50000, 5000000),
					'presentation' => fake()->numberBetween(80000, 10000000),
					default       => fake()->numberBetween(20000, 3000000),
				};

				// Datas
				$createdAt = $now->subDays(fake()->numberBetween(0, 180))
					->subMinutes(fake()->numberBetween(0, 1440));
				$updatedAt = $createdAt->addMinutes(fake()->numberBetween(0, 10080));
				$lastAcc   = fake()->boolean(60) ? $updatedAt->addMinutes(fake()->numberBetween(0, 4320)) : null;

				// Expiração (eventualmente no passado p/ cobrir fluxo expirado)
				$expiresAt = $maybe(function () use ($updatedAt) {
					return fake()->boolean(85)
						? $updatedAt->addDays(fake()->numberBetween(30, 365))
						: $updatedAt->subDays(fake()->numberBetween(1, 60));
				});

				// Nome e caminho
				$base   = Arr::random($nameSeeds);
				$suffix = Arr::random(['v1', 'v2', 'final', 'draft', 'review', 'signed', null]);
				$fname  = trim($base . ($suffix ? "_{$suffix}" : '')) . '.' . $ext;
				$fpath  = 'leads/' . $lead->id . '/files/' . Str::lower(Str::uuid()->toString()) . '.' . $ext;

				// Regras de permissão octais (6+ dígitos 0-7)
				$perm = implode('', array_map(fn() => (string) random_int(0, 7), range(1, 6)));

				// Listas de atores (validadas por existência de usuários)
				$pickActors = function (int $max) use ($userIds) {
					if (!$userIds) return [];
					$count = fake()->numberBetween(0, min($max, count($userIds)));
					return $count > 0 ? Arr::random($userIds, $count) : [];
				};
				$executors = $pickActors(3);
				$editors   = $pickActors(3);
				$viewers   = $pickActors(5);

				$row = [
					'id'                    => (string) Str::uuid(),
					PJC::COL_LD_ID          => $lead->id,
					DC::COL_FL_NM           => $maybe(fn() => $fname),
					DC::COL_FL_PT           => $fpath,
					'extension'             => $ext,
					DC::COL_MM_TP           => $mime,
					DC::COL_LA              => $lastAcc?->toDateTimeString(),
					'type'                  => $category,
					'size'                  => $size,
					'description'           => $maybe(fn() => fake()->sentence(10)),
					'notes'                 => $maybe(fn() => fake()->sentence(12)),
					DC::COL_DL_CT           => fake()->numberBetween(0, 250),
					DC::COL_FL_SZ           => number_format((float) $size, 4, '.', ''), // decimal(16,4)
					DC::COL_EXP_DT          => $expiresAt?->toDateTimeString(),
					DC::COL_PERM_RLS        => $perm,
					'executors'             => $json($executors),
					'editors'               => $json($editors),
					'viewers'               => $json($viewers),
					'created_at'            => $createdAt->toDateTimeString(),
					'updated_at'            => $updatedAt->toDateTimeString(),
				];

				// Auditoria (se existir)
				if (Schema::hasColumn(DC::TABLE_LD_FILES, DC::COL_TABLE_CREATOR)) {
					$row[DC::COL_TABLE_CREATOR] = $userIds ? Arr::random($userIds) : null;
				}
				if (Schema::hasColumn(DC::TABLE_LD_FILES, DC::COL_TABLE_UPDATER)) {
					$row[DC::COL_TABLE_UPDATER] = $userIds ? Arr::random($userIds) : null;
				}

				// Remove apenas nulls (manter 0/false)
				$rows[] = $row;
				$inserted++;
			}
		}

		if (!$rows) {
			$this->command?->info('LeadFileSeeder: nada a inserir.');
			return;
		}

		DB::transaction(function () use ($rows) {
			foreach (array_chunk($rows, self::CHUNK_SIZE) as $chunk) {
				DB::table(DC::TABLE_LD_FILES)->insert($chunk);
			}
		});

		$this->command?->info("LeadFileSeeder: {$inserted} arquivos inseridos em " . DC::TABLE_LD_FILES . ".");
	}
}
