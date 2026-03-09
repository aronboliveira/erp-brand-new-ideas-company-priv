<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{AppModuleType, IndicatorTechnicalLevel};
use App\Models\TrainingType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class TrainingTypeSeeder extends Seeder
{
	private OutputInterface $out;

	public function run(): void
	{
		$this->initOutput();

		$this->out->writeln('<info>Seeding: ' . DC::TABLE_TRAINING_TYPES . '</info>');

		$existingCodes = DB::table(DC::TABLE_TRAINING_TYPES)
			->whereNotNull('code')
			->pluck('code')
			->filter()
			->map(fn($v) => (string) $v)
			->all();

		$existingCodes = array_fill_keys($existingCodes, true);

		$rawCandidates = [];
		$topics = [
			'Onboarding',
			'Compliance',
			'Security Awareness',
			'Process Excellence',
			'Customer Success',
			'Leadership',
			'Time Management',
			'Quality Standards',
			'Incident Response',
			'Data Protection',
			'Service Desk',
			'DevOps Foundations',
			'Database Essentials',
			'Infrastructure Basics',
			'Sales Playbook',
			'Finance Ops',
		];

		$idx = 0;

		foreach (AppModuleType::cases() as $moduleCase) {
			foreach (IndicatorTechnicalLevel::cases() as $levelCase) {
				$k = random_int(2, 16);

				for ($i = 0; $i < $k; $i++) {
					$idx++;

					$module = $moduleCase->value;
					$level = $levelCase->value;

					[$min, $max] = $this->durationsByLevel($levelCase);

					$topic = $topics[array_rand($topics)];
					$moduleLabel = $moduleCase->label();
					$levelLabel = $levelCase->label();

					$code = $this->makeUniqueCode($moduleCase, $levelCase, $existingCodes);

					$rawCandidates[] = [
						'code' => $code,
						'name' => "{$moduleLabel} — {$topic} ({$levelLabel})",
						'description' => "TrainingType for {$moduleLabel} focusing on {$topic}, aimed at {$levelLabel}.",
						'module' => $module,
						'level' => $level,
						PJC::COL_MIN_DR => $min,
						PJC::COL_MAX_DR => $max,
						'rules' => $this->buildRules($min, $max),
						'attachments' => $this->maybeArray(35, ['handbook' => 'internal', 'format' => 'slides']),
						'tags' => $this->maybeArray(70, [
							Str::slug($module, '_'),
							Str::slug($topic, '_'),
							'training_type',
						]),
					];
				}
			}
		}

		$rawCount = count($rawCandidates);
		$cap = $rawCount + ($rawCount % 64);

		$countOpt = $this->getCountOption();
		$target = $countOpt !== null ? min($cap, $countOpt) : $cap;

		$this->out->writeln(
			'<comment>'
				. OutputFormatter::escape("Candidates: {$rawCount} | Cap (raw + raw%64): {$cap} | Target: {$target}")
				. '</comment>'
		);

		while (count($rawCandidates) < $target && $rawCount > 0) {
			$base = $rawCandidates[array_rand($rawCandidates)];

			$base['code'] = $this->makeUniqueCode(
				AppModuleType::normalize((string) ($base['module'] ?? null)),
				IndicatorTechnicalLevel::normalize((string) ($base['level'] ?? null)),
				$existingCodes
			);

			$base['name'] = (string) ($base['name'] ?? 'Training Type') . ' — ' . Str::upper(Str::random(4));
			$rawCandidates[] = $base;
		}

		if (count($rawCandidates) > $target) {
			$rawCandidates = array_slice($rawCandidates, 0, $target);
		}

		$created = 0;
		$skipped = 0;

		foreach ($rawCandidates as $i => $row) {
			$code = (string) ($row['code'] ?? '');

			if ($code === '' || isset($existingCodes[$code])) {
				$skipped++;
				continue;
			}

			try {
				TrainingType::query()->create($row);
				$existingCodes[$code] = true;
				$created++;

				if ($created === 1 || ($created % 50) === 0) {
					$this->out->writeln('<info>' . OutputFormatter::escape("Created: {$created}") . '</info>');
				}
			} catch (\Throwable $ex) {
				$skipped++;
				$this->out->writeln(
					'<error>' . OutputFormatter::escape("Failed creating TrainingType ({$code}): " . $ex->getMessage()) . '</error>'
				);
			}
		}

		$this->out->writeln(
			'<info>'
				. OutputFormatter::escape("Done. Created={$created}, Skipped={$skipped}")
				. '</info>'
		);
	}

	private function initOutput(): void
	{
		try {
			if ($this->command instanceof \Illuminate\Console\Command) {
				$out = $this->command->getOutput();
				if ($out instanceof OutputInterface) {
					$this->out = $out;
					return;
				}
			}
		} catch (\Throwable) {
		}

		$this->out = new NullOutput();
	}

	private function getCountOption(): ?int
	{
		if (!($this->command instanceof \Illuminate\Console\Command)) {
			return null;
		}

		if (!$this->command->hasOption('count')) {
			return null;
		}

		$raw = $this->command->option('count');
		if (!is_numeric($raw)) {
			return null;
		}

		$n = (int) $raw;
		return $n >= 1 ? $n : null;
	}

	private function makeUniqueCode(AppModuleType $module, IndicatorTechnicalLevel $level, array &$existingCodes): string
	{
		$prefix = 'TRN-'
			. Str::upper(Str::substr(preg_replace('/[^a-z0-9]/i', '', $module->value), 0, 6))
			. '-L' . $level->value . '-';

		for ($i = 0; $i < 32; $i++) {
			$code = $prefix . Str::upper(Str::random(8));
			if (!isset($existingCodes[$code])) {
				return $code;
			}
		}

		$fallback = $prefix . Str::upper(Str::random(16));
		if (!isset($existingCodes[$fallback])) {
			return $fallback;
		}

		return $prefix . Str::upper(Str::uuid()->toString());
	}

	private function durationsByLevel(IndicatorTechnicalLevel $level): array
	{
		return match ($level) {
			IndicatorTechnicalLevel::None => [null, null],
			IndicatorTechnicalLevel::Beginner => ['00:30:00', '02:00:00'],
			IndicatorTechnicalLevel::Intermediate => ['01:00:00', '04:00:00'],
			IndicatorTechnicalLevel::Advanced => ['02:00:00', '06:00:00'],
			IndicatorTechnicalLevel::Expert => ['03:00:00', '08:00:00'],
		};
	}

	private function buildRules(?string $min, ?string $max): array
	{
		$rules = [];

		if (is_string($min) && trim($min) !== '') {
			$rules['duration_min'] = $min;
		}
		if (is_string($max) && trim($max) !== '') {
			$rules['duration_max'] = $max;
		}

		return $rules;
	}

	private function maybeArray(int $probPct, array $value): array
	{
		return random_int(1, 100) <= $probPct ? $value : [];
	}
}
