<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	CompaniesConstants as CC,
	DatabaseConstants as DC,
	UsersConstants as UC
};
use App\Enums\AssetType;
use App\Models\Asset;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class AssetsSeeder extends Seeder
{
	private const SEED = 20251212;

	public function run(): void
	{
		fake()->seed(self::SEED);

		if (!Schema::hasTable(DC::TABLE_AST)) {
			$this->command?->warn('AssetsSeeder: tabela ' . DC::TABLE_AST . ' ausente. Seeder abortado.');
			return;
		}

		if (!Schema::hasTable(DC::TABLE_EMPLOYEES)) {
			$this->command?->warn('AssetsSeeder: tabela ' . DC::TABLE_EMPLOYEES . ' ausente. Seeder abortado.');
			return;
		}

		if (!Schema::hasTable(DC::TABLE_USERS)) {
			$this->command?->warn('AssetsSeeder: tabela ' . DC::TABLE_USERS . ' ausente. Seeder abortado.');
			return;
		}

		// Carrega employees + tipo de usuário associado
		$employeeRows = DB::table(DC::TABLE_EMPLOYEES . ' as e')
			->leftJoin(DC::TABLE_USERS . ' as u', 'e.' . UC::COL_USER_ID, '=', 'u.id')
			->select(
				'e.id as employee_id',
				'u.id as user_id',
				'u.' . UC::COL_TP . ' as user_type',
				'u.' . UC::COL_NM . ' as user_name'
			)
			->get();

		if ($employeeRows->count() === 0) {
			$this->command?->warn('AssetsSeeder: nenhum employee encontrado em ' . DC::TABLE_EMPLOYEES . '. Nada a semear.');
			return;
		}

		$employees           = [];
		$privilegedEmployees = [];

		foreach ($employeeRows as $row) {
			$employeeId = (string) ($row->employee_id ?? '');
			if ($employeeId === '') {
				continue;
			}

			$userType = null;
			if (isset($row->user_type) && is_string($row->user_type)) {
				$userType = strtolower(trim($row->user_type));
			}

			$userName = null;
			if (isset($row->user_name) && is_string($row->user_name)) {
				$userName = trim($row->user_name);
			}

			$info = [
				'employee_id' => $employeeId,
				'user_id'     => (string) ($row->user_id ?? ''),
				'user_type'   => $userType,
				'user_name'   => $userName !== '' ? $userName : ('Funcionário ' . substr($employeeId, 0, 8)),
			];

			$employees[] = $info;

			if ($userType !== null) {
				$normalized = str_replace(' ', '_', $userType);
				if (in_array($normalized, ['admin', 'super_admin', 'company'], true)) {
					$privilegedEmployees[] = $info;
				}
			}
		}

		if ($employees === []) {
			$this->command?->warn('AssetsSeeder: não há employees válidos para semear.');
			return;
		}

		$employeeCount = count($employees);

		// Multiplicador opcional --count
		$multiplier = 1;
		if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
			$opt = (int) $this->command->option('count');
			if ($opt > 0) {
				$multiplier = $opt;
			}
		}

		// Regra global do projeto: mínimo 4 * n registros (n = base entities)
		// $targetTotal = 4 * $employeeCount * $multiplier; // ORIGINAL — unbounded
		$targetTotal = min(2, 4 * $employeeCount * $multiplier); // HARD CAP

		// Amostra de 10% dos employees (no mínimo 1), considerando o multiplicador
		$sampleSize = (int) floor($employeeCount * 0.1);
		if ($sampleSize < 1) {
			$sampleSize = 1;
		}
		$sampleSize *= $multiplier;
		if ($sampleSize > $employeeCount) {
			$sampleSize = $employeeCount;
		}

		$sampleEmployees = $sampleSize >= $employeeCount
			? $employees
			: (array) Arr::random($employees, $sampleSize);

		// Categorias de produto/serviço (para coluna category)
		$categoryIds = [];
		if (Schema::hasTable(DC::TABLE_PROD_SERV_CATS)) {
			$categoryIds = DB::table(DC::TABLE_PROD_SERV_CATS)
				->pluck('id')
				->map(static fn($id) => (string) $id)
				->all();
		}

		// Possíveis orders e transactions
		$orderIds = [];
		if (Schema::hasTable(DC::TABLE_ORDERS)) {
			$orderIds = DB::table(DC::TABLE_ORDERS)
				->pluck('id')
				->map(static fn($id) => (string) $id)
				->all();
		}

		$transactionIds = [];
		if (Schema::hasTable(DC::TABLE_TRS)) {
			$transactionIds = DB::table(DC::TABLE_TRS)
				->pluck('id')
				->map(static fn($id) => (string) $id)
				->all();
		}

		$attachmentMimes = [
			'application/pdf',
			'image/png',
			'image/jpeg',
		];

		$tagsPool = [
			'it',
			'hr',
			'finance',
			'operations',
			'administration',
			'r_d',
			'sales',
			'marketing',
			'shared',
			'leased',
			'owned',
			'depreciable',
			'capex',
			'opex',
		];

		// Controle para garantir que todos os tipos do enum sejam usados
		$assetTypeCases = AssetType::cases();
		$unusedTypes    = array_map(static fn(AssetType $case) => $case->value, $assetTypeCases);
		$allTypes       = $unusedTypes;

		$pickMany = static function (array $source, int $min, int $max): array {
			$total = count($source);
			if ($total === 0) {
				return [];
			}

			if ($min < 0) {
				$min = 0;
			}

			if ($max < $min) {
				$max = $min;
			}

			if ($min === 0 && $max === 0) {
				return [];
			}

			if ($max > $total) {
				$max = $total;
			}

			$take = fake()->numberBetween($min, $max);
			if ($take <= 0) {
				return [];
			}

			$result = Arr::random($source, $take);
			return is_array($result) ? array_values($result) : [$result];
		};

		$output  = new ConsoleOutput();
		$created = 0;

		/*
         * Laço principal:
         * foreach <10% employees> => foreach <1-4 categorias> => foreach <1-8 tipos>
         */
		foreach ($sampleEmployees as $employeeInfo) {
			if ($created >= $targetTotal) {
				break;
			}

			$employeeId   = $employeeInfo['employee_id'];
			$userType     = $employeeInfo['user_type'];
			$employeeName = $employeeInfo['user_name'];

			// Normaliza tipo de usuário para checagem de privilégio
			$normalizedType = $userType !== null
				? str_replace(' ', '_', strtolower($userType))
				: null;

			$requiresSigner = !in_array($normalizedType, ['admin', 'super_admin', 'company'], true);

			// Seleciona de 1 a 4 categorias para este employee
			$employeeCategories = [];
			if ($categoryIds !== []) {
				$catCount          = fake()->numberBetween(1, 4);
				$employeeCategoriesRaw = Arr::random(
					$categoryIds,
					min($catCount, count($categoryIds))
				);
				$employeeCategories = is_array($employeeCategoriesRaw)
					? array_values($employeeCategoriesRaw)
					: [$employeeCategoriesRaw];
			} else {
				// Sem categorias cadastradas, ainda assim semeamos assets
				$employeeCategories = [null];
			}

			foreach ($employeeCategories as $categoryId) {
				if ($created >= $targetTotal) {
					break;
				}

				// Para cada categoria, de 1 a 8 variações de tipo
				$typeIterations = fake()->numberBetween(1, 8);

				for ($i = 0; $i < $typeIterations && $created < $targetTotal; $i++) {
					// Garante que todos os tipos sejam usados ao menos uma vez
					if ($unusedTypes !== []) {
						$typeValue = array_pop($unusedTypes);
					} else {
						$typeValue = Arr::random($allTypes);
					}

					$typeEnum = AssetType::normalize($typeValue);

					// Datas de compra/suporte
					$purchaseDate = Carbon::now()
						->subDays(fake()->numberBetween(30, 365 * 3))
						->startOfDay();

					$supportedDate = $purchaseDate->copy()
						->addDays(fake()->numberBetween(30, 365 * 2))
						->startOfDay();

					// Valores
					$amount = fake()->randomFloat(2, 150.00, 50000.00);

					// Ordem / transação opcionais
					$orderId = null;
					if ($orderIds !== [] && fake()->boolean(40)) {
						$orderId = Arr::random($orderIds);
					}

					$transactionId = null;
					if ($transactionIds !== [] && fake()->boolean(30)) {
						$transactionId = Arr::random($transactionIds);
					}

					// Assinatura (sign_by / sign_by_name)
					$signBy     = null;
					$signByName = null;

					if ($requiresSigner) {
						// Dono não é privilegiado -> precisamos de um signer privilegiado, se existir
						if ($privilegedEmployees !== []) {
							$signer   = Arr::random($privilegedEmployees);
							$signBy   = $signer['employee_id'];
							$signByName = $signer['user_name'];
						} else {
							// Fallback defensivo: usa algum employee qualquer, mas mantém campo preenchido
							$signer     = Arr::random($employees);
							$signBy     = $signer['employee_id'];
							$signByName = $signer['user_name'];
						}
					} else {
						// Dono já é privilegiado: eventualmente podemos registrar um signer formal
						if ($privilegedEmployees !== [] && fake()->boolean(25)) {
							$signer   = Arr::random($privilegedEmployees);
							$signBy   = $signer['employee_id'];
							$signByName = $signer['user_name'];
						}
					}

					// Attachments
					$attachments = [];
					$attachmentCount = fake()->numberBetween(0, 3);
					for ($a = 0; $a < $attachmentCount; $a++) {
						$ext  = fake()->randomElement(['pdf', 'png', 'jpg']);
						$mime = Arr::random($attachmentMimes);

						$attachments[] = [
							'path' => 'assets/' . $employeeId . '/' . Str::uuid() . '.' . $ext,
							'name' => 'Comprovante ' . ($a + 1) . ' - ' . $employeeName,
							'mime' => $mime,
							'size' => fake()->numberBetween(10_000, 900_000),
						];
					}

					// Tags
					$tags = [];
					$tagCount = fake()->numberBetween(1, 6);
					for ($t = 0; $t < $tagCount; $t++) {
						$tags[] = Arr::random($tagsPool);
					}

					$tags[] = 'type_' . $typeEnum->value;
					$tags[] = $typeEnum->isDepreciable() ? 'depreciable' : 'non_depreciable';

					// Metadata
					$metadata = [
						'seeded_at'       => Carbon::now()->toDateTimeString(),
						'seed_source'     => 'AssetsSeeder',
						'department_hint' => AssetType::department($typeEnum),
						'lifespan_years'  => $typeEnum->typicalLifespanYears(),
						'owner_user_type' => $normalizedType,
					];

					// Serial único (probabilisticamente muito seguro)
					$serial = 'AST-' . strtoupper(Str::random(10));

					$name = sprintf(
						'%s - %s de %s',
						ucfirst(str_replace('_', ' ', $typeEnum->value)),
						fake()->randomElement(['Empresa', 'Filial', 'Departamento']),
						$employeeName
					);

					$purpose = fake()->sentence(8);

					// Console output antes de criar
					// $output->writeln(sprintf(
					// 	'Criando Asset "%s" (tipo: %s, dono: %s, categoria: %s, valor: %.2f, signer: %s)',
					// 	$name,
					// 	$typeEnum->value,
					// 	substr($employeeId, 0, 8),
					// 	$categoryId ?: 'nenhuma',
					// 	$amount,
					// 	$signBy !== null ? substr($signBy, 0, 8) : 'não exigido'
					// ));

					Asset::query()->create([
						'serial'               => $serial,
						'category'             => $categoryId,
						'type'                 => $typeEnum->value,
						'name'                 => $name,
						UC::COL_EMP_ID         => $employeeId,
						CC::COL_PRC_DT         => $purchaseDate,
						CC::COL_SPT_DT         => $supportedDate,
						'amount'               => $amount,
						'description'          => fake()->boolean(70) ? fake()->realTextBetween(80, 220) : null,
						'purpose'              => $purpose,
						'order'                => $orderId,
						'transaction'          => $transactionId,
						BC::COL_SIGN_BY        => $signBy,
						BC::COL_SIGN_BY_NAME   => $signByName,
						'attachments'          => $attachments,
						'metadata'             => $metadata,
						'tags'                 => $tags,
					]);

					$created++;
				}
			}
		}

		/*
         * Se por acaso a lógica de 10% * categorias * tipos não atingir o mínimo 64 * n,
         * gera assets extras genéricos até bater o alvo.
         */
		while ($created < $targetTotal) {
			$employeeInfo = Arr::random($employees);

			$employeeId   = $employeeInfo['employee_id'];
			$userType     = $employeeInfo['user_type'];
			$employeeName = $employeeInfo['user_name'];

			$normalizedType = $userType !== null
				? str_replace(' ', '_', strtolower($userType))
				: null;

			$requiresSigner = !in_array($normalizedType, ['admin', 'super_admin', 'company'], true);

			$categoryId = null;
			if ($categoryIds !== [] && fake()->boolean(60)) {
				$categoryId = Arr::random($categoryIds);
			}

			if ($unusedTypes !== []) {
				$typeValue = array_pop($unusedTypes);
			} else {
				$typeValue = Arr::random($allTypes);
			}

			$typeEnum = AssetType::normalize($typeValue);

			$purchaseDate = Carbon::now()
				->subDays(fake()->numberBetween(30, 365 * 3))
				->startOfDay();

			$supportedDate = $purchaseDate->copy()
				->addDays(fake()->numberBetween(30, 365 * 2))
				->startOfDay();

			$amount = fake()->randomFloat(2, 150.00, 50000.00);

			$orderId = null;
			if ($orderIds !== [] && fake()->boolean(40)) {
				$orderId = Arr::random($orderIds);
			}

			$transactionId = null;
			if ($transactionIds !== [] && fake()->boolean(30)) {
				$transactionId = Arr::random($transactionIds);
			}

			$signBy     = null;
			$signByName = null;

			if ($requiresSigner) {
				if ($privilegedEmployees !== []) {
					$signer   = Arr::random($privilegedEmployees);
					$signBy   = $signer['employee_id'];
					$signByName = $signer['user_name'];
				} else {
					$signer   = Arr::random($employees);
					$signBy   = $signer['employee_id'];
					$signByName = $signer['user_name'];
				}
			}

			$attachments = [];
			$attachmentCount = fake()->numberBetween(0, 2);
			for ($a = 0; $a < $attachmentCount; $a++) {
				$ext  = fake()->randomElement(['pdf', 'png', 'jpg']);
				$mime = Arr::random($attachmentMimes);

				$attachments[] = [
					'path' => 'assets/' . $employeeId . '/' . Str::uuid() . '.' . $ext,
					'name' => 'Comprovante extra ' . ($a + 1) . ' - ' . $employeeName,
					'mime' => $mime,
					'size' => fake()->numberBetween(10_000, 900_000),
				];
			}

			$tags = [];
			$tagCount = fake()->numberBetween(1, 4);
			for ($t = 0; $t < $tagCount; $t++) {
				$tags[] = Arr::random($tagsPool);
			}
			$tags[] = 'type_' . $typeEnum->value;

			$metadata = [
				'seeded_at'       => Carbon::now()->toDateTimeString(),
				'seed_source'     => 'AssetsSeeder',
				'department_hint' => AssetType::department($typeEnum),
				'lifespan_years'  => $typeEnum->typicalLifespanYears(),
				'owner_user_type' => $normalizedType,
				'filler'          => true,
			];

			$serial  = 'AST-' . strtoupper(Str::random(10));
			$name    = ucfirst(str_replace('_', ' ', $typeEnum->value)) . ' - extra';
			$purpose = fake()->sentence(6);

			// $output->writeln(sprintf(
			// 	'Criando Asset extra "%s" (tipo: %s, dono: %s, categoria: %s, valor: %.2f, signer: %s)',
			// 	$name,
			// 	$typeEnum->value,
			// 	substr($employeeId, 0, 8),
			// 	$categoryId ?: 'nenhuma',
			// 	$amount,
			// 	$signBy !== null ? substr($signBy, 0, 8) : 'não exigido'
			// ));

			Asset::query()->create([
				'serial'               => $serial,
				'category'             => $categoryId,
				'type'                 => $typeEnum->value,
				'name'                 => $name,
				UC::COL_EMP_ID         => $employeeId,
				CC::COL_PRC_DT         => $purchaseDate,
				CC::COL_SPT_DT         => $supportedDate,
				'amount'               => $amount,
				'description'          => fake()->boolean(60) ? fake()->realTextBetween(60, 180) : null,
				'purpose'              => $purpose,
				'order'                => $orderId,
				'transaction'          => $transactionId,
				BC::COL_SIGN_BY        => $signBy,
				BC::COL_SIGN_BY_NAME   => $signByName,
				'attachments'          => $attachments,
				'metadata'             => $metadata,
				'tags'                 => $tags,
			]);

			$created++;
		}

		$this->command?->info(sprintf(
			'AssetsSeeder: %d registros criados em %s (mínimo alvo: %d; employees: %d; multiplicador: %d).',
			$created,
			DC::TABLE_AST,
			$targetTotal,
			$employeeCount,
			$multiplier
		));
	}
}
