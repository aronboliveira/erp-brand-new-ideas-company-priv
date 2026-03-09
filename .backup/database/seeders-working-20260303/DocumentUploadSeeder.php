<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, UsersConstants as UC};
use App\Enums\{AppModuleType, EvaluationStatus, MimeType};
use App\Models\DocumentUpload;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Support\{Arr, Str};
use Symfony\Component\Console\Output\ConsoleOutput;
use Carbon\Carbon;

class DocumentUploadsSeeder extends Seeder
{
	private const SEED = 20251215;

	/**
	 * Tipos de documento preferenciais por módulo
	 *
	 * @var array<string, MimeType[]>
	 */
	private const MODULE_MIME_MAP = [
		'financial' => [
			MimeType::APPLICATION_XLSX,
			MimeType::APPLICATION_MSEXCEL,
			MimeType::TEXT_CSV,
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_JSON,
			MimeType::APPLICATION_XML,
			MimeType::APPLICATION_DOCX,
		],
		'sales' => [
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_DOCX,
			MimeType::APPLICATION_XLSX,
			MimeType::TEXT_CSV,
			MimeType::TEXT_PLAIN,
		],
		'crm' => [
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_DOCX,
			MimeType::TEXT_PLAIN,
			MimeType::TEXT_MARKDOWN,
			MimeType::APPLICATION_JSON,
		],
		'hrm' => [
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_DOCX,
			MimeType::APPLICATION_ODT,
			MimeType::TEXT_PLAIN,
			MimeType::APPLICATION_RTF,
		],
		'projects' => [
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_DOCX,
			MimeType::APPLICATION_PPTX,
			MimeType::APPLICATION_XLSX,
			MimeType::TEXT_MARKDOWN,
		],
		'management' => [
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_PPTX,
			MimeType::APPLICATION_DOCX,
			MimeType::APPLICATION_XLSX,
		],
		'inventory' => [
			MimeType::APPLICATION_XLSX,
			MimeType::TEXT_CSV,
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_JSON,
		],
		'support' => [
			MimeType::TEXT_PLAIN,
			MimeType::TEXT_MARKDOWN,
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_JSON,
		],
		'database' => [
			MimeType::APPLICATION_SQL,
			MimeType::TEXT_PLAIN,
			MimeType::APPLICATION_JSON,
			MimeType::APPLICATION_XML,
		],
		'infrastructure' => [
			MimeType::TEXT_PLAIN,
			MimeType::TEXT_MARKDOWN,
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_JSON,
		],
		'marketing' => [
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_PPTX,
			MimeType::APPLICATION_DOCX,
			MimeType::TEXT_MARKDOWN,
		],
		'custom' => [
			MimeType::APPLICATION_PDF,
			MimeType::TEXT_MARKDOWN,
			MimeType::TEXT_PLAIN,
			MimeType::APPLICATION_JSON,
		],
		'landing_page' => [
			MimeType::TEXT_HTML,
			MimeType::TEXT_CSS,
			MimeType::TEXT_JS,
			MimeType::APPLICATION_JSON,
		],
		'user' => [
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_DOCX,
			MimeType::TEXT_PLAIN,
		],
		'customer' => [
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_DOCX,
			MimeType::APPLICATION_XLSX,
		],
		'vendor' => [
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_DOCX,
			MimeType::APPLICATION_XLSX,
		],
		'product' => [
			MimeType::APPLICATION_PDF,
			MimeType::TEXT_MARKDOWN,
			MimeType::APPLICATION_JSON,
		],
		'proposal' => [
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_DOCX,
			MimeType::APPLICATION_PPTX,
		],
		'invoice' => [
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_XLSX,
			MimeType::TEXT_CSV,
		],
		'bill' => [
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_XLSX,
			MimeType::TEXT_CSV,
		],
		'account' => [
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_XLSX,
			MimeType::APPLICATION_JSON,
		],
		'other' => [
			MimeType::APPLICATION_PDF,
			MimeType::TEXT_PLAIN,
			MimeType::TEXT_MARKDOWN,
		],
	];

	/**
	 * Lista padrão de types documentais caso módulo não tenha mapa
	 *
	 * @var MimeType[]
	 */
	private const DEFAULT_DOC_MIMES = [
		MimeType::APPLICATION_PDF,
		MimeType::APPLICATION_DOCX,
		MimeType::APPLICATION_XLSX,
		MimeType::TEXT_MARKDOWN,
		MimeType::TEXT_PLAIN,
		MimeType::TEXT_CSV,
		MimeType::APPLICATION_JSON,
		MimeType::APPLICATION_XML,
		MimeType::APPLICATION_PPTX,
		MimeType::APPLICATION_ODT,
		MimeType::APPLICATION_ODS,
		MimeType::APPLICATION_ODP,
	];

	public function run(): void
	{
		fake()->seed(self::SEED);

		$output = new ConsoleOutput();

		if (!Schema::hasTable(DC::TABLE_DOC_UP)) {
			$this->command?->warn(
				'DocumentUploadsSeeder: tabela ' . DC::TABLE_DOC_UP . ' ausente. Seeder abortado.'
			);
			return;
		}

		$modules = AppModuleType::cases();
		$modulesCount = count($modules);

		if ($modulesCount === 0) {
			$this->command?->warn(
				'DocumentUploadsSeeder: nenhum AppModuleType definido.'
			);
			return;
		}

		// ids de documentos e usuários (leitura via DB::table)
		$documentIds = Schema::hasTable(DC::TABLE_DOCS)
			? DB::table(DC::TABLE_DOCS)->pluck('id')->all()
			: [];

		$userIds = Schema::hasTable(DC::TABLE_USERS)
			? DB::table(DC::TABLE_USERS)->pluck('id')->all()
			: [];

		// fator opcional via --count (multiplicador de N)
		$multiplier = 1;
		if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
			$opt = (int) $this->command->option('count');
			if ($opt > 0) {
				$multiplier = $opt;
			}
		}

		// Regra: total de entidades sempre = 64 x N
		$perModuleTarget = 4 * $multiplier;
		$targetTotal = $perModuleTarget * $modulesCount;

		$statuses = EvaluationStatus::cases();
		$totalCreated = 0;

		$output->writeln(sprintf(
			'<info>DocumentUploadsSeeder:</info> alvo = %d registros (%d por módulo x %d módulos)',
			$targetTotal,
			$perModuleTarget,
			$modulesCount
		));

		foreach ($modules as $moduleEnum) {
			$moduleValue = $moduleEnum->value;

			$mimeCandidates = $this->mimeCandidatesForModule($moduleEnum);
			if ($mimeCandidates === []) {
				$this->command?->warn(sprintf(
					'DocumentUploadsSeeder: módulo "%s" sem MimeTypes documentais. Ignorando módulo.',
					$moduleValue
				));
				continue;
			}

			// entre 2 e 16 tipos por módulo
			$numTypesForModule = fake()->numberBetween(
				2,
				min(16, count($mimeCandidates))
			);

			/** @var MimeType[] $selectedMimes */
			$selectedMimes = Arr::random($mimeCandidates, $numTypesForModule);
			if ($selectedMimes instanceof MimeType) {
				$selectedMimes = [$selectedMimes];
			}

			// statuses embaralhados para garantir cobertura
			$statusList = collect($statuses)->shuffle()->all();
			$statusIndex = 0;

			$createdForModule = 0;

			while ($createdForModule < $perModuleTarget) {
				foreach ($selectedMimes as $mimeEnum) {
					$remaining = $perModuleTarget - $createdForModule;
					if ($remaining <= 0) {
						break 2;
					}

					// entre 1 e 8 uploads para este type nesta passagem
					$uploadsForType = fake()->numberBetween(1, min(8, $remaining));

					for ($i = 0; $i < $uploadsForType && $createdForModule < $perModuleTarget; $i++) {
						$statusEnum = $statusList[$statusIndex % count($statusList)];
						$statusIndex++;

						[$attributes, $failureMeta] = $this->buildUploadAttributes(
							$moduleEnum,
							$mimeEnum,
							$statusEnum,
							$documentIds,
							$userIds
						);

						// Garantir unicidade contextual de document/sha256/md5
						[$attributes['document'], $attributes['sha256'], $attributes['md5']] =
							$this->ensureUniqueIdentifiers(
								$attributes['document'],
								$attributes['sha256'],
								$attributes['md5']
							);

                        // Criação via Model para aplicar casts + booted
						/** @var DocumentUpload $upload */
						$upload = new DocumentUpload();
						$upload->fill($attributes);
						$upload->save();

						// Campos de tracking de falha (não estão em $fillable)
						if ($failureMeta['has_failure']) {
							$upload->setAttribute(DC::COL_FL_AT, $failureMeta['failed_at']);
							$upload->setAttribute(DC::COL_FLD_RS, $failureMeta['reason']);
							$upload->setAttribute(DC::COL_RTR_CT, $failureMeta['retry_count']);
							$upload->setAttribute(DC::COL_LST_RTR_AT, $failureMeta['last_retry_at']);
							$upload->setAttribute(DC::COL_ER_LG, $failureMeta['error_log']);
							$upload->save();
						}

						$createdForModule++;
						$totalCreated++;

						$output->writeln(sprintf(
							'DOC_UP: module=%s, type=%s, status=%s, name="%s", doc="%s"',
							$moduleValue,
							$mimeEnum->value,
							$statusEnum->value,
							Str::limit($attributes['name'], 30, '…'),
							Str::limit($attributes['document'], 40, '…')
						));
					}
				}
			}

			$this->command?->info(sprintf(
				'DocumentUploadsSeeder: módulo "%s" => %d uploads criados.',
				$moduleValue,
				$createdForModule
			));
		}

		$this->command?->info(sprintf(
			'DocumentUploadsSeeder concluído: %d registros criados em %s.',
			$totalCreated,
			DC::TABLE_DOC_UP
		));
	}

	/**
	 * Retorna lista de MimeTypes documentais para um módulo.
	 *
	 * @return MimeType[]
	 */
	private function mimeCandidatesForModule(AppModuleType $module): array
	{
		$key = $module->value;

		$candidates = self::MODULE_MIME_MAP[$key] ?? self::DEFAULT_DOC_MIMES;

		// Garante apenas tipos que MimeType::isDocument() considera documentais
		$filtered = [];
		foreach ($candidates as $mime) {
			if ($mime->isDocument()) {
				$filtered[] = $mime;
			}
		}

		return $filtered;
	}

	/**
	 * Gera atributos de um upload + metadados de falha.
	 *
	 * @return array{0:array,1:array}
	 */
	private function buildUploadAttributes(
		AppModuleType $module,
		MimeType $mime,
		EvaluationStatus $status,
		array $documentIds,
		array $userIds
	): array {
		$moduleValue = $module->value;
		$ext = $mime->getExtension();

		// nome e papel genéricos, mas coerentes
		$name = sprintf(
			'%s %s #%d',
			ucfirst($moduleValue),
			Str::title(str_replace('_', ' ', $mime->getExtension())),
			fake()->numberBetween(1, 9999)
		);

		$possibleRoles = [
			'owner',
			'uploader',
			'manager',
			'reviewer',
			'approver',
			'auditor',
			'controller',
			'viewer',
		];
		$role = Arr::random($possibleRoles);

		// descrição: 80–90% de chance de existir
		$description = fake()->boolean(15)
			? null
			: fake()->sentence(fake()->numberBetween(8, 18));

		// path lógico do documento
		$documentPathBase = sprintf(
			'uploads/%s/%s-%s',
			$moduleValue,
			Str::slug(fake()->words(fake()->numberBetween(2, 4), true), '_'),
			Str::lower(Str::random(8))
		);
		$documentPath = $documentPathBase . '.' . $ext;

		// required_storage (bytes) por tipo e módulo
		[$minBytes, $maxBytes] = $this->storageRangeForMime($mime, $module);
		$requiredStorage = (float) fake()->numberBetween($minBytes, $maxBytes);

		// progress baseado no status
		$progress = match ($status) {
			EvaluationStatus::Draft,
			EvaluationStatus::NotStarted => (float) fake()->randomFloat(2, 0, 15),

			EvaluationStatus::Pending => (float) fake()->randomFloat(2, 0, 40),

			EvaluationStatus::InProgress,
			EvaluationStatus::Active => (float) fake()->randomFloat(2, 10, 95),

			EvaluationStatus::Completed,
			EvaluationStatus::Archived,
			EvaluationStatus::Accept => 100.0,

			EvaluationStatus::Cancelled,
			EvaluationStatus::Decline,
			EvaluationStatus::Suspended,
			EvaluationStatus::Expired => (float) fake()->randomFloat(2, 0, 100),

			EvaluationStatus::Undefined => (float) fake()->randomFloat(2, 0, 5),
		};

		// object_url: baixa chance de ser null
		$objectUrl = fake()->boolean(10)
			? null
			: sprintf(
				'https://files.mock.local/%s/%s',
				$moduleValue,
				basename($documentPath)
			);

		// criptografia
		$isEncrypted = fake()->boolean(30);
		$encAlg = null;
		if ($isEncrypted) {
			$encAlg = Arr::random([
				'AES-256-GCM',
				'AES-128-CBC',
				'ChaCha20-Poly1305',
			]);
		}

		// malware
		$isMalwareFree = !fake()->boolean(5); // ~95% limpo

		// política (sempre array, mas nem sempre com dados)
		$policy = [];
		if (fake()->boolean(70)) {
			$policy = [
				'classification'      => Arr::random(['public', 'internal', 'confidential', 'restricted']),
				'retention_days'      => fake()->numberBetween(30, 5 * 365),
				'requires_encryption' => $isEncrypted,
				'requires_av_scan'    => true,
				'allowed_roles'       => Arr::random(
					['admin', 'manager', 'auditor', 'owner', 'support'],
					fake()->numberBetween(1, 3)
				),
			];
		}

		// metadata (sempre array, às vezes vazio)
		$metadata = [];
		if (fake()->boolean(75)) {
			$metadata = [
				'source'            => Arr::random(['web', 'api', 'mobile', 'batch']),
				'original_filename' => basename($documentPath),
				'uploaded_from_ip'  => fake()->ipv4(),
				'user_agent'        => fake()->userAgent(),
			];
		}

		// tags
		$tags = [];
		if (fake()->boolean(80)) {
			$tagCount = fake()->numberBetween(1, 5);
			for ($i = 0; $i < $tagCount; $i++) {
				$tags[] = Str::slug(fake()->word(), '_');
			}
			$tags = array_values(array_unique($tags));
		}

		// scan de malware (sempre array, às vezes contendo info)
		$malwareScan = [];
		if (fake()->boolean(80)) {
			$malwareScan = [
				'engine'     => Arr::random(['mock-av', 'clamav', 'internal-av']),
				'version'    => sprintf('%d.%d.%d', fake()->numberBetween(0, 3), fake()->numberBetween(0, 9), fake()->numberBetween(0, 20)),
				'scanned_at' => Carbon::now()->subMinutes(fake()->numberBetween(0, 720))->toIso8601String(),
				'result'     => $isMalwareFree ? 'clean' : Arr::random(['infected', 'suspicious']),
			];
		}

		// hashes em minúsculo, formas válidas
		$sha256 = strtolower(bin2hex(random_bytes(32))); // 64 hex chars
		$md5    = strtolower(bin2hex(random_bytes(16))); // 32 hex chars

		// relacionamento opcional com Document
		$docId = null;
		if ($documentIds !== [] && fake()->boolean(70)) {
			$docId = Arr::random($documentIds);
		}

		// uploader opcional
		$userId = null;
		if ($userIds !== [] && fake()->boolean(85)) {
			$userId = Arr::random($userIds);
		}

		// tracking de falhas
		$hasFailure = !$isMalwareFree
			|| in_array($status, [
				EvaluationStatus::Cancelled,
				EvaluationStatus::Decline,
				EvaluationStatus::Suspended,
				EvaluationStatus::Expired,
			], true);

		$failedAt     = null;
		$failedReason = null;
		$retryCount   = 0;
		$lastRetryAt  = null;
		$errorLog     = [];

		if ($hasFailure) {
			$failedAt = Carbon::now()->subMinutes(fake()->numberBetween(10, 1440));
			$failedReason = Arr::random([
				'malware_detected',
				'policy_violation',
				'storage_quota_exceeded',
				'unsupported_format',
				'integrity_check_failed',
			]);
			$retryCount = fake()->numberBetween(0, 3);
			$lastRetryAt = $retryCount > 0
				? $failedAt->copy()->addMinutes(fake()->numberBetween(1, 60))
				: null;

			$errorLog = [
				[
					'timestamp' => $failedAt->toIso8601String(),
					'level'     => 'warning',
					'message'   => 'Upload evaluation failed.',
					'context'   => [
						'reason'      => $failedReason,
						'module'      => $moduleValue,
						'mime'        => $mime->value,
						'status'      => $status->value,
						'document'    => $documentPath,
						'is_encrypted' => $isEncrypted,
					],
				],
			];
		}

		$attributes = [
			'name'                     => $name,
			'role'                     => $role,
			'description'              => $description,
			'document'                 => $documentPath,
			'module'                   => $moduleValue,
			'type'                     => $mime->value,
			'status'                   => $status->value,
			'progress'                 => $progress,
			DC::COL_RQ_SPC             => $requiredStorage,
			DC::COL_OBJ_URL            => $objectUrl,
			'sha256'                   => $sha256,
			'md5'                      => $md5,
			DC::COL_IS_ENC             => $isEncrypted,
			DC::COL_ENC_ALG            => $encAlg,
			DC::COL_MW_FREE            => $isMalwareFree,
			DC::COL_DOC_ID             => $docId,
			UC::COL_USER_ID            => $userId,
			'policy'                   => $policy,
			'metadata'                 => $metadata,
			'tags'                     => $tags,
			DC::COL_MW_SCAN            => $malwareScan,
		];

		$failureMeta = [
			'has_failure'   => $hasFailure,
			'failed_at'     => $failedAt,
			'reason'        => $failedReason,
			'retry_count'   => $retryCount,
			'last_retry_at' => $lastRetryAt,
			'error_log'     => $errorLog,
		];

		return [$attributes, $failureMeta];
	}

	/**
	 * Garante unicidade contextual de document/sha256/md5 com do/while + exists.
	 *
	 * @return array{0:string,1:string,2:string}
	 */
	private function ensureUniqueIdentifiers(
		string $document,
		string $sha256,
		string $md5
	): array {
		do {
			$exists = DB::table(DC::TABLE_DOC_UP)
				->where('document', $document)
				->orWhere('sha256', $sha256)
				->orWhere('md5', $md5)
				->exists();

			if (!$exists) {
				break;
			}

			// Se já existir, gera novos hashes e um novo sufixo no path
			$suffix   = Str::lower(Str::random(4));
			$dir      = dirname($document);
			$filename = pathinfo($document, PATHINFO_FILENAME);
			$ext      = pathinfo($document, PATHINFO_EXTENSION);

			$document = sprintf(
				'%s/%s_%s.%s',
				$dir,
				$filename,
				$suffix,
				$ext
			);

			$sha256 = strtolower(bin2hex(random_bytes(32)));
			$md5    = strtolower(bin2hex(random_bytes(16)));
		} while (true);

		return [$document, $sha256, $md5];
	}

	/**
	 * Faixa de tamanho em bytes para o arquivo, por MimeType / módulo.
	 *
	 * @return array{0:int,1:int}
	 */
	private function storageRangeForMime(MimeType $mime, AppModuleType $module): array
	{
		// valores base (em bytes)
		$smallMin = 10 * 1024;          // 10 KB
		$smallMax = 512 * 1024;         // 512 KB
		$medMin   = 512 * 1024;         // 512 KB
		$medMax   = 5 * 1024 * 1024;    // 5 MB
		$bigMin   = 5 * 1024 * 1024;    // 5 MB
		$bigMax   = 50 * 1024 * 1024;   // 50 MB

		return match ($mime) {
			MimeType::APPLICATION_PDF,
			MimeType::APPLICATION_DOCX,
			MimeType::APPLICATION_MSWORD,
			MimeType::APPLICATION_ODT,
			MimeType::APPLICATION_RTF => [$medMin, $medMax],

			MimeType::APPLICATION_XLSX,
			MimeType::APPLICATION_MSEXCEL,
			MimeType::APPLICATION_ODS => [$medMin, $bigMax],

			MimeType::TEXT_CSV,
			MimeType::TEXT_PLAIN,
			MimeType::TEXT_MARKDOWN,
			MimeType::APPLICATION_JSON,
			MimeType::APPLICATION_XML,
			MimeType::APPLICATION_SQL => [$smallMin, $medMax],

			MimeType::APPLICATION_PPTX,
			MimeType::APPLICATION_MSPPT,
			MimeType::APPLICATION_ODP => [$medMin, $bigMax],

			default => match ($module) {
				AppModuleType::Financial,
				AppModuleType::Sales,
				AppModuleType::CRM,
				AppModuleType::HRM => [$smallMin, $medMax],

				AppModuleType::Marketing,
				AppModuleType::Projects,
				AppModuleType::Management => [$medMin, $bigMax],

				default => [$smallMin, $medMax],
			},
		};
	}
}
