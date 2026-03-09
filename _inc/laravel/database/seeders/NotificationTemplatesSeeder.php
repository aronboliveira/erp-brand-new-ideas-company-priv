<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	MessagesConstants as MC
};
use App\Enums\NotificationTemplateType;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class NotificationTemplatesSeeder extends Seeder
{
	public function run(): void
	{
		if (!Schema::hasTable(DC::TABLE_NOTIFICATION_TEMPLATES)) {
			$this->command?->warn('NotificationTemplatesSeeder: tabela de templates não existe. Seeder abortado.');
			return;
		}

		$types = NotificationTemplateType::cases();
		if (empty($types)) {
			$this->command?->warn('NotificationTemplatesSeeder: enum NotificationTemplateType sem casos. Nada a semear.');
			return;
		}

		$typeCount = count($types);

		// N: multiplicador base
		$multiplier = $this->command instanceof \Illuminate\Console\Command
			&& $this->command->hasOption('count')
			? max(1, (int) $this->command->option('count'))
			: max(1, $typeCount);

		$total = 64 * $multiplier;

		// Distribuição aproximada por tipo
		$perTypeBase = intdiv($total, $typeCount);
		$remainder   = $total % $typeCount;

		// Leitura de possíveis criadores com DB::table (leitura-only)
		$creatorIds = [];
		if (Schema::hasTable(DC::TABLE_USERS)) {
			$creatorIds = DB::table(DC::TABLE_USERS)
				->select('id')
				->pluck('id')
				->all();
		}

		$faker   = fake();
		$created = 0;
		$HARD_CAP = 2; // HARD CAP guard

		$categoriesPool = [
			'system',
			'billing',
			'reminder',
			'marketing',
			'security',
			'hr',
			'project',
			'support',
		];

		$plansPool = [
			'free',
			'starter',
			'basic',
			'pro',
			'enterprise',
		];

		$langPool = [
			'en',
			'pt-BR',
			'es',
			'fr',
			'de',
		];

		foreach ($types as $index => $typeEnum) {
			if ($created >= $HARD_CAP) break; // HARD CAP guard
			$targetForType = $perTypeBase + ($index < $remainder ? 1 : 0);
			if ($targetForType <= 0) {
				continue;
			}

			for ($i = 0; $i < $targetForType; $i++) {
				// -------- Garantia de unicidade (type + slug) --------
				$attempt = 0;
				$maxAttempts = 25;
				$slugExists = false;
				$baseName   = null;
				$slug       = null;

				do {
					$attempt++;

					$baseName = $faker->sentence($faker->numberBetween(2, 4));

					// Em parte dos casos, prefixa com o tipo para dar contexto
					if ($faker->boolean(40)) {
						$baseName = ucfirst($typeEnum->value) . ' ' . $baseName;
					}

					$slug = Str::slug($baseName);

					// Se começar a repetir muito, força distinção
					if ($attempt > 3) {
						$slug = Str::slug($slug . '-' . $attempt . '-' . $i);
					}

					$slugExists = DB::table(DC::TABLE_NOTIFICATION_TEMPLATES)
						->where('type', $typeEnum->value)
						->where('slug', $slug)
						->exists();
				} while ($slugExists && $attempt < $maxAttempts);

				if ($slugExists) {
					// Não conseguiu um slug único razoavelmente; evita loop infinito
					$this->command?->warn(sprintf(
						'NotificationTemplatesSeeder: não foi possível gerar slug único para type="%s" após %d tentativas; pulando 1 registro.',
						$typeEnum->value,
						$maxAttempts
					));
					continue;
				}

				// -------- Demais campos “mockados” --------

				$name = $baseName;

				$categories = collect($categoriesPool)
					->shuffle()
					->take($faker->numberBetween(0, 3))
					->values()
					->all(); // manter shape consistente

				$excludedPlans = $faker->boolean(40)
					? collect($plansPool)
					->shuffle()
					->take($faker->numberBetween(1, 3))
					->values()
					->all()
					: [];

				$rules = [];
				if ($faker->boolean(70)) {
					$rulesCandidates = [
						[
							'field' => 'subject',
							'max'   => 160,
						],
						[
							'field' => 'body',
							'max'   => 2048,
						],
						[
							'field'         => 'attachments',
							'allowed_mimes' => ['pdf', 'jpg', 'png'],
						],
						[
							'field'   => 'priority',
							'allowed' => ['low', 'normal', 'high'],
						],
					];

					$rules = collect($rulesCandidates)
						->shuffle()
						->take($faker->numberBetween(1, 3))
						->values()
						->all();
				}

				$availableLangs = $faker->boolean(75)
					? collect($langPool)
					->shuffle()
					->take($faker->numberBetween(1, 3))
					->values()
					->all()
					: [];

				$now = Carbon::now();
				$availableFrom = $now
					->subDays($faker->numberBetween(0, 15))
					->addMinutes($faker->numberBetween(0, 1440));

				$creatorId = !empty($creatorIds) && $faker->boolean(70)
					? Arr::random($creatorIds)
					: null;

				$disabled = $faker->boolean(20);

				// -------- Payload (sem array_filter removendo nulls) --------

				$payload = [
					'name'                 => $name,
					'slug'                 => $slug,
					'type'                 => $typeEnum->value,
					'description'          => $faker->boolean(80) ? $faker->realText(180) : null,
					AC::COL_AV_FROM        => $availableFrom->toDateTimeString(),
					AC::COL_DSB            => $disabled,
					'categories'           => $categories,
					MC::COL_EX_PLN         => $excludedPlans,
					'rules'                => $rules,
					MC::COL_AV_LG          => $availableLangs,
					DC::COL_TABLE_CREATOR  => $creatorId,
					// DC::COL_TABLE_UPDATER pode ficar null; o boot/casts lidam depois
				];
				// (new \Symfony\Component\Console\Output\ConsoleOutput())->writeln("Generating notification template {$payload['slug']}{$payload['name']} ({$payload['type']})");
				// Usa o Model para disparar booted(), casts e normalizações
				NotificationTemplate::query()->create($payload);
				$created++;
			}
		}

		$this->command?->info(sprintf(
			'NotificationTemplatesSeeder: %d templates criados (64 x %d).',
			$created,
			$multiplier
		));
	}
}
