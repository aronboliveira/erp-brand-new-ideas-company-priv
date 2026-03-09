<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC,
	UsersConstants as UC
};
use App\Enums\{
	AppModuleType,
	LogType,
	UserType
};
use App\Models\LeadActivityLog;
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class LeadActivityLogSeeder extends Seeder
{
	private const DEFAULT_COUNT = 2; // was 128

	public function run(): void
	{
		$faker = fake();

		$count = $this->resolveSeedCount(self::DEFAULT_COUNT);

		$pickId = function (string $table): ?string {
			try {
				return DB::table($table)->inRandomOrder()->value('id');
			} catch (\Throwable) {
				return null;
			}
		};

		$userIdSample = $pickId(DC::TABLE_USERS);
		$leadIdSample = $pickId(DC::TABLE_LEADS);

		if (!$userIdSample || !$leadIdSample) {
			$this->command?->warn(
				'LeadActivityLogSeeder: é necessário ter ao menos 1 usuário e 1 lead para gerar logs.'
			);
			return;
		}

		$userTypes   = UserType::values();
		$logTypes    = LogType::values();
		$moduleTypes = AppModuleType::values();

		$categoryPool = [
			'lead',
			'crm',
			'pipeline',
			'security',
			'system',
			'ui',
			'api',
			'job',
			'mail',
			'notification',
			'database',
			'support',
		];

		$tagPool = [
			'incoming',
			'outgoing',
			'manual',
			'api',
			'imported',
			'system',
			'security',
			'error',
			'warning',
			'info',
			'performance',
			'audit',
		];

		DB::transaction(function () use (
			$faker,
			$count,
			$pickId,
			$userTypes,
			$logTypes,
			$moduleTypes,
			$categoryPool,
			$tagPool
		) {
			/**
			 * Bitmask para garantir >= 64 variações:
			 * bits 0–5 controlam:
			 *   0 => user_type
			 *   1 => remark
			 *   2 => module
			 *   3 => label
			 *   4 => description
			 *   5 => related_categories
			 */
			for ($i = 0; $i < $count; $i++) {
				try {
					$mask = $i % 64;

					$hasUserType   = (bool) ($mask & (1 << 0));
					$hasRemark     = (bool) ($mask & (1 << 1));
					$hasModule     = (bool) ($mask & (1 << 2));
					$hasLabel      = (bool) ($mask & (1 << 3));
					$hasDesc       = (bool) ($mask & (1 << 4));
					$hasCategories = (bool) ($mask & (1 << 5));

					$userId = $pickId(DC::TABLE_USERS) ?? $pickId(DC::TABLE_USERS);
					$leadId = $pickId(DC::TABLE_LEADS) ?? $pickId(DC::TABLE_LEADS);

					if (!$userId || !$leadId) {
						continue;
					}

					$logTypeValue = Arr::random($logTypes);
					$moduleValue  = Arr::random($moduleTypes);

					$userTypeValue = $hasUserType
						? Arr::random($userTypes)
						: null;

					$remark = $hasRemark
						? $faker->realText($faker->numberBetween(40, 180))
						: null;

					$label = $hasLabel
						? ucfirst($faker->words($faker->numberBetween(2, 4), true))
						: null;

					$description = $hasDesc
						? $faker->paragraph($faker->numberBetween(1, 3))
						: null;

					$relatedCategories = $hasCategories
						? $faker->randomElements($categoryPool, $faker->numberBetween(1, 4))
						: null;

					$tags = $faker->boolean(70)
						? $faker->randomElements($tagPool, $faker->numberBetween(1, 5))
						: null;

					$errorLog = null;
					if (in_array($logTypeValue, [
						LogType::Error->value,
						LogType::Critical->value,
						LogType::Alert->value,
						LogType::Emergency->value,
						LogType::Security->value,
						LogType::System->value,
						LogType::Database->value,
					], true)) {
						if ($faker->boolean(80)) {
							$errorLog = [
								'message'   => $faker->sentence(),
								'code'      => $faker->numberBetween(1000, 9999),
								'file'      => $faker->randomElement([
									'LeadService.php',
									'PipelineService.php',
									'NotificationJob.php',
									'SecurityMiddleware.php',
								]),
								'line'      => $faker->numberBetween(10, 300),
								'trace_id'  => (string) Str::uuid(),
								'extra'     => [
									'lead_id'  => $leadId,
									'user_id'  => $userId,
									'severity' => $faker->randomElement(['low', 'medium', 'high', 'critical']),
								],
								'occurred_at' => Carbon::now()
									->subMinutes($faker->numberBetween(0, 60))
									->toIso8601String(),
							];
						}
					}

					if ($label === null && $faker->boolean(40)) {
						$label = match ($logTypeValue) {
							LogType::Mail->value          => 'Lead mail event',
							LogType::Notification->value  => 'Lead notification',
							LogType::Security->value      => 'Security log for lead',
							LogType::Audit->value         => 'Lead audit record',
							LogType::Performance->value   => 'Lead performance metric',
							LogType::Api->value           => 'Lead API interaction',
							LogType::Job->value           => 'Background job for lead',
							default                       => ucfirst($logTypeValue) . ' log',
						};
					}

					if ($description === null && $faker->boolean(35)) {
						$description = $faker->sentence(12) . ' (lead activity log).';
					}
					$mod = $hasModule ? $moduleValue : AppModuleType::Other->value;
					$payload = [
						UC::COL_USER_ID     => $userId,
						PJC::COL_LD_ID      => $leadId,
						AC::COL_LOG_TP      => $logTypeValue,
						'remark'            => $remark,
						AC::COL_MD          => $mod,
						'label'             => $label,
						'description'       => $description,
						DC::COL_RL_CAT      => $relatedCategories,
						PJC::COL_TAGS       => $tags,
						DC::COL_ER_LG       => $errorLog,
					];

					if ($userTypeValue !== null) {
						$payload[UC::COL_U_TP] = $userTypeValue;
					}

					if (rand(0, 100) < 60) {
						$payload[DC::COL_TABLE_CREATOR] = $userId;
					}
					// (new \Symfony\Component\Console\Output\ConsoleOutput)->writeln("Criando Log de Atividade {$label} de Lead {$leadId} para usuário {$userId} do módulo {$mod}");
					LeadActivityLog::query()->create($payload);
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		});
	}

	/**
	 * Resolve o valor de --count de forma segura:
	 * - Garante que $this->command é uma instância de Illuminate\Console\Command;
	 * - Verifica se a opção "count" existe na definição do comando;
	 * - Garante que o valor é numérico e > 0;
	 * - Caso contrário, retorna o default.
	 */
	private function resolveSeedCount(int $default): int
	{
		$command = $this->command;

		if (!$command instanceof Command) {
			return $default;
		}

		try {
			$definition = $command->getDefinition();
		} catch (\Throwable) {
			return $default;
		}

		if (!$definition->hasOption('count')) {
			return $default;
		}

		$raw = $command->option('count');

		if ($raw === null || $raw === '') {
			return $default;
		}

		if (!is_numeric($raw)) {
			return $default;
		}

		$value = (int) $raw;

		if ($value <= 0) {
			return $default;
		}

		return $value;
	}
}
