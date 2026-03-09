<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC};
use App\Enums\{CaseStatus, EvaluationStatus};
use App\Models\ProjectStage as Pst;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Symfony\Component\Console\Output\ConsoleOutput;

class ProjectStagesSeeder extends Seeder
{
	use EnsuresSystemUser;

	private ConsoleOutput $out;

	private const ATTEMPT_LIMIT = 128;

	public function __construct()
	{
		$this->out = new ConsoleOutput();
	}

	public function run(): void
	{
		$faker = fake('pt_BR');

		DB::transaction(function () use ($faker): void {
			$creatorId = (string) $this->ensureSystemUser();

			$names = $this->buildNames($faker);

			$this->out->writeln('[ProjectStagesSeeder] names=' . count($names) . ' (logEven=' . ($this->logEven(count($names)) ? 'yes' : 'no') . ')');

			$order = 0;

			foreach ($names as $nm) {
				$nm = trim((string) $nm);
				if ($nm === '') continue;

				$color = $faker->hexColor() . $faker->randomElement(['', '88', 'cc']);

				$notes = $faker->boolean(35)
					? [
						['title' => $faker->sentence(4), 'text' => $faker->sentence(12)],
						['title' => $faker->sentence(3), 'text' => $faker->sentence(10)],
					]
					: null;

				$involved = $faker->boolean(30)
					? [
						$creatorId,
						(string) $faker->uuid(),
					]
					: [$creatorId];

				$metadata = $faker->boolean(45)
					? [
						'ui' => [
							'badge' => $faker->word(),
							'priority' => $faker->randomElement(['low', 'medium', 'high']),
						],
						'seed' => [
							'by' => 'ProjectStagesSeeder',
							'v' => 2,
						],
					]
					: null;

				$positioning = $faker->boolean(55)
					? [
						'x' => $faker->numberBetween(0, 1200),
						'y' => $faker->numberBetween(0, 800),
						'column' => $faker->numberBetween(0, 8),
					]
					: null;

				$this->out->writeln(sprintf(
					'[ProjectStage] name="%s" order=%d color=%s notes=%s involved=%d meta=%s pos=%s',
					$nm,
					$order,
					$color,
					$notes === null ? 'NULL' : 'JSON',
					is_array($involved) ? count($involved) : 0,
					$metadata === null ? 'NULL' : 'JSON',
					$positioning === null ? 'NULL' : 'JSON'
				));

				try {
					$m = Pst::query()->firstOrNew(['name' => $nm]);

					$m->setAttribute('name', $nm);
					$m->setAttribute('description', $faker->boolean(55) ? $faker->sentence(14) : null);
					$m->setAttribute('color', $color);
					$m->setAttribute('order', $order++);
					$m->setAttribute('notes', $notes);
					$m->setAttribute('involved', $involved);
					$m->setAttribute('metadata', $metadata);
					$m->setAttribute('positioning', $positioning);

					// Audit (HasNullableAuditColumns => HasAuditFields on model)
					$m->setAttribute(DC::COL_TABLE_CREATOR, $creatorId);
					$m->setAttribute(DC::COL_TABLE_UPDATER, null);

					$m->save();
				} catch (\Throwable $e) {
					Log::warning(self::class . ' failed saving ProjectStage', [
						'file' => $e->getFile(),
						'line' => $e->getLine(),
						'error' => $e->getMessage(),
						'name' => $nm,
					]);
				}
			}
		}, 3);
	}

	private function buildNames($faker): array
	{
		$lang = 'pt-br';

		$eval = [];
		try {
			foreach (EvaluationStatus::cases() as $c) {
				if (method_exists($c, 'label') && is_callable([$c, 'label'])) {
					try {
						$eval[] = (string) $c->label($lang);
					} catch (\Throwable $e) {
						Log::debug(self::class . ' failed getting EvaluationStatus label for ' . $c->name, [
							'file' => $e->getFile(),
							'line' => $e->getLine(),
							'error' => $e->getMessage(),
						]);
						$eval[] = (string) $c->value;
					}
					continue;
				}

				$labels = method_exists(EvaluationStatus::class, 'labels') ? (array) EvaluationStatus::labels($lang) : [];
				$eval[] = (string) ($labels[$c->value] ?? $c->value);
			}
		} catch (\Throwable $e) {
			Log::warning(self::class . ' failed building EvaluationStatus labels', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
		}

		$case = [];
		try {
			foreach (CaseStatus::cases() as $c)
				$case[] = (string) $c->label($lang);
		} catch (\Throwable $e) {
			Log::warning(self::class . ' failed building CaseStatus labels', [
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				'error' => $e->getMessage(),
			]);
		}

		$names = array_values(array_unique(array_merge($eval, $case)));

		$attempts = 0;
		while (!$this->logEven(count($names)) && $attempts++ < self::ATTEMPT_LIMIT) {
			$names[] = trim((string) $faker->words(random_int(1, 3), true));
			$names = array_values(array_unique($names));
		}

		if (!$this->logEven(count($names)))
			$this->out->writeln('[ProjectStagesSeeder] WARNING: attempt limit hit while fixing logEven condition; proceeding anyway.');

		return $names;
	}

	private function logEven(int $count): bool
	{
		if ($count <= 1) return false;

		// Mirror the requested condition: log(count($names)) % 2 == 0
		// In PHP, "%" is integer-based; cast defensively to avoid float surprises.
		$v = (int) floor(log((float) $count));
		return ($v % 2) === 0;
	}
}
