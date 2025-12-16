<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\{AppModuleType, FieldType, MimeType};
use App\Models\CustomField;
use Illuminate\Database\Seeder;
use Illuminate\Support\{Arr, Str};
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;

class CustomFieldsSeeder extends Seeder
{
	private const SEED = 20251212;

	public function run(): void
	{
		fake()->seed(self::SEED);

		if (!Schema::hasTable(DC::TABLE_CUSTOM_FIELDS)) {
			$this->command?->warn(
				'CustomFieldsSeeder: tabela ' . DC::TABLE_CUSTOM_FIELDS . ' ausente. Seeder abortado.'
			);
			return;
		}

		$fieldTypes = FieldType::cases();
		$modules    = AppModuleType::cases();

		if ($fieldTypes === [] || $modules === []) {
			$this->command?->warn(
				'CustomFieldsSeeder: nenhum FieldType ou AppModuleType disponível. Seeder abortado.'
			);
			return;
		}

		$fieldLabels       = FieldType::labels();
		$fieldPlaceholders = FieldType::placeholders();

		$moduleCount = count($modules);

		$multiplier = 1;
		if ($this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')) {
			$opt = (int) $this->command->option('count');
			if ($opt > 0) {
				$multiplier = $opt;
			}
		}

		$targetTotal = 2 * $moduleCount * $multiplier;

		$output  = new ConsoleOutput();
		$created = 0;

		foreach ($fieldTypes as $fieldType) {
			foreach ($modules as $moduleEnum) {
				$baseIterations = fake()->numberBetween(1, 8);
				$iterations     = $baseIterations * $multiplier;

				for ($i = 1; $i <= $iterations; $i++) {
					$nameLabel  = $fieldLabels[$fieldType->value] ?? Str::headline($fieldType->value);
					$moduleName = $moduleEnum->label();

					$name = sprintf(
						'%s - %s #%d',
						$moduleName,
						$nameLabel,
						$i
					);

					$required = fake()->boolean(50);

					$placeholder = null;
					$pattern     = null;
					$readonly    = null;
					$multiline   = null;
					$multiple    = null;
					$autocap     = null;
					$autocomplete = null;
					$autocorrect  = null;
					$min        = null;
					$max        = null;
					$step       = null;
					$minlength  = null;
					$maxlength  = null;
					$rows       = null;
					$cols       = null;
					$wrap       = null;
					$spellcheck = null;
					$options    = null;
					$optgroups  = null;
					$accepts    = null;
					$size       = null;

					$tags = [
						'module_' . $moduleEnum->value,
						'type_' . $fieldType->value,
						'custom',
					];

					$aria = [];
					$dataset = [];
					$selectors = [];

					$placeholder = $fieldPlaceholders[$fieldType->value] ?? null;

					switch ($fieldType) {
						case FieldType::Text:
						case FieldType::Email:
						case FieldType::Tel:
						case FieldType::Url:
						case FieldType::Search:
						case FieldType::Password:
							$minlength = 0;
							$maxlength = fake()->numberBetween(64, 255);
							$spellcheck = fake()->randomElement(['false', 'true', 'default']);

							if ($fieldType === FieldType::Password) {
								$placeholder = $placeholder ?? '********';
							}

							$aria['aria-label'] = $name;
							break;

						case FieldType::Textarea:
							$multiline  = true;
							$minlength  = 0;
							$maxlength  = fake()->numberBetween(256, 2048);
							$rows       = fake()->numberBetween(3, 8);
							$cols       = fake()->numberBetween(30, 80);
							$wrap       = fake()->randomElement(['soft', 'hard']);
							$spellcheck = fake()->randomElement(['false', 'true', 'default']);
							$placeholder = $placeholder ?? 'Digite um texto mais longo...';
							$size = [
								'width'  => '100%',
								'height' => fake()->numberBetween(4, 10) . 'em',
							];

							$aria['aria-label'] = $name;
							$aria['aria-describedby'] = 'cf-help-' . Str::slug($moduleEnum->value . '-' . $fieldType->value, '_');
							break;

						case FieldType::Number:
						case FieldType::Range:
							$min  = (string) fake()->numberBetween(0, 10);
							$max  = (string) fake()->numberBetween(20, 1000);
							$step = fake()->boolean()
								? '1'
								: (string) fake()->randomFloat(1, 0.1, 5.0);

							if ((float) $min > (float) $max) {
								[$min, $max] = [$max, $min];
							}

							$aria['aria-label'] = $name;
							break;

						case FieldType::Date:
							$min  = '2000-01-01';
							$max  = '2099-12-31';
							$step = '1';
							$aria['aria-label'] = $name;
							break;

						case FieldType::Time:
							$min  = '00:00';
							$max  = '23:59';
							$step = fake()->randomElement(['1', '5', '15']);
							$aria['aria-label'] = $name;
							break;

						case FieldType::DateTimeLocal:
							$min  = '2000-01-01T00:00';
							$max  = '2099-12-31T23:59';
							$step = fake()->randomElement(['1', '5', '15']);
							$aria['aria-label'] = $name;
							break;

						case FieldType::Month:
							$min  = '2000-01';
							$max  = '2099-12';
							$step = '1';
							$aria['aria-label'] = $name;
							break;

						case FieldType::Week:
							$min  = '2000-W01';
							$max  = '2099-W52';
							$step = '1';
							$aria['aria-label'] = $name;
							break;

						case FieldType::Select:
							$groupCount = fake()->numberBetween(1, 3);
							$optgroups  = [];

							for ($g = 1; $g <= $groupCount; $g++) {
								$optgroups[] = [
									'label' => $moduleName . ' Group ' . $g,
								];
							}

							$optionsCount = fake()->numberBetween(3, 10);
							$options      = [];

							for ($o = 1; $o <= $optionsCount; $o++) {
								$groupLabel = Arr::random($optgroups)['label'] ?? null;
								$value      = Str::slug($moduleEnum->value . '_' . $fieldType->value . '_' . $o, '_');
								$text       = Str::headline($value);

								$options[] = [
									'text'     => $text,
									'value'    => $value,
									'selected' => $o === 1,
									'disabled' => fake()->boolean(5),
									'group'    => $groupLabel,
								];
							}

							$multiple = fake()->boolean(30);
							$aria['aria-label'] = $name;
							break;

						case FieldType::RadioGroup:
							$optionsCount = fake()->numberBetween(2, 6);
							$options      = [];

							for ($o = 1; $o <= $optionsCount; $o++) {
								$value = Str::slug($fieldType->value . '_' . $o, '_');
								$text  = Str::headline($value);

								$options[] = [
									'text'     => $text,
									'value'    => $value,
									'selected' => $o === 1,
									'disabled' => fake()->boolean(10),
									'group'    => null,
								];
							}

							$aria['aria-label'] = $name;
							break;

						case FieldType::Checkbox:
							$aria['aria-label'] = $name;
							break;

						case FieldType::Color:
							$pattern = '^#(?:[0-9a-fA-F]{3}){1,2}$';
							$aria['aria-label'] = $name;
							break;

						case FieldType::File:
							$acceptOptions = [
								MimeType::IMAGE_PNG->value,
								MimeType::IMAGE_JPEG->value,
								MimeType::APPLICATION_PDF->value,
								'.docx',
								'.xlsx',
								'.zip',
							];
							$accepts = Arr::random($acceptOptions, fake()->numberBetween(1, 4));
							if (!is_array($accepts)) {
								$accepts = [$accepts];
							}

							$multiple = fake()->boolean(40);
							$aria['aria-label'] = $name;
							$aria['aria-describedby'] = 'cf-file-hint-' . Str::slug($moduleEnum->value, '_');
							break;
					}

					if ($placeholder === null && in_array($fieldType, [
						FieldType::Text,
						FieldType::Email,
						FieldType::Tel,
						FieldType::Url,
						FieldType::Search,
					], true)) {
						$placeholder = 'Informe ' . strtolower($nameLabel);
					}

					if ($required) {
						$aria['aria-required'] = 'true';
					}

					$dataset['data-module']      = $moduleEnum->value;
					$dataset['data-field-type']  = $fieldType->value;
					$dataset['data-field-key']   = Str::slug($moduleEnum->value . '-' . $fieldType->value, '_');
					$dataset['data-seeded']      = 'true';

					$selectors[] = '.cf-module-' . Str::slug($moduleEnum->value, '-');
					$selectors[] = '.cf-type-' . Str::slug($fieldType->value, '-');

					if ($size === null && in_array($fieldType, [
						FieldType::Text,
						FieldType::Email,
						FieldType::Search,
					], true)) {
						$size = [
							'width' => '100%',
						];
					}

					$defaultValue = null;

					if ($required && $fieldType === FieldType::Checkbox) {
						$defaultValue = '1';
					} elseif ($required && in_array($fieldType, [
						FieldType::Text,
						FieldType::Email,
						FieldType::Tel,
						FieldType::Search,
						FieldType::Url,
					], true)) {
						$defaultValue = 'Sample ' . $nameLabel;
					}

					$output->writeln(sprintf(
						'Criando CustomField "%s" [type=%s, module=%s, required=%s]',
						$name,
						$fieldType->value,
						$moduleEnum->value,
						$required ? 'sim' : 'não'
					));

					CustomField::query()->create([
						'name'          => $name,
						'type'          => $fieldType->value,
						'module'        => $moduleEnum->value,
						'description'   => fake()->boolean(60) ? fake()->sentence(12) : null,
						'tags'          => $tags,
						'default'       => $defaultValue,
						'placeholder'   => $placeholder,
						'pattern'       => $pattern,
						'readonly'      => $readonly ?? fake()->boolean(10),
						'required'      => $required,
						'multiline'     => $multiline ?? false,
						'multiple'      => $multiple ?? false,
						'autocapitalize' => $autocap ?? false,
						'autocomplete'  => $autocomplete ?? fake()->boolean(40),
						'autocorrect'   => $autocorrect ?? false,
						'disabled'      => fake()->boolean(5),
						'min'           => $min,
						'max'           => $max,
						'step'          => $step,
						'minlength'     => $minlength,
						'maxlength'     => $maxlength,
						'rows'          => $rows,
						'cols'          => $cols,
						'wrap'          => $wrap,
						'spellcheck'    => $spellcheck,
						'options'       => $options,
						'optgroups'     => $optgroups,
						'aria'          => $aria,
						'dataset'       => $dataset,
						'accepts'       => $accepts,
						'selectors'     => $selectors,
						'size'          => $size,
					]);

					$created++;
				}
			}
		}

		if ($created < $targetTotal) {
			$extraNeeded = $targetTotal - $created;

			$this->command?->warn(sprintf(
				'CustomFieldsSeeder: gerando %d campos extras para atingir o mínimo (%d).',
				$extraNeeded,
				$targetTotal
			));

			for ($e = 1; $e <= $extraNeeded; $e++) {
				$fieldType = Arr::random($fieldTypes);
				$moduleEnum = Arr::random($modules);

				$nameLabel  = $fieldLabels[$fieldType->value] ?? Str::headline($fieldType->value);
				$moduleName = $moduleEnum->label();

				$name = sprintf(
					'%s - %s (extra #%d)',
					$moduleName,
					$nameLabel,
					$e
				);

				$required = fake()->boolean(40);
				$placeholder = $fieldPlaceholders[$fieldType->value] ?? ('Informe ' . strtolower($nameLabel));

				$tags = [
					'module_' . $moduleEnum->value,
					'type_' . $fieldType->value,
					'extra',
				];

				$aria = [
					'aria-label'    => $name,
					'aria-required' => $required ? 'true' : 'false',
				];

				$dataset = [
					'data-module'     => $moduleEnum->value,
					'data-field-type' => $fieldType->value,
					'data-seeded'     => 'true',
					'data-extra'      => 'true',
				];

				$selectors = [
					'.cf-module-' . Str::slug($moduleEnum->value, '-'),
					'.cf-type-' . Str::slug($fieldType->value, '-'),
				];

				$size = [
					'width' => '100%',
				];

				$output->writeln(sprintf(
					'Criando CustomField extra "%s" [type=%s, module=%s]',
					$name,
					$fieldType->value,
					$moduleEnum->value
				));

				CustomField::query()->create([
					'name'          => $name,
					'type'          => $fieldType->value,
					'module'        => $moduleEnum->value,
					'description'   => fake()->boolean(50) ? fake()->sentence(10) : null,
					'tags'          => $tags,
					'default'       => null,
					'placeholder'   => $placeholder,
					'pattern'       => null,
					'readonly'      => fake()->boolean(5),
					'required'      => $required,
					'multiline'     => false,
					'multiple'      => false,
					'autocapitalize' => false,
					'autocomplete'  => fake()->boolean(40),
					'autocorrect'   => false,
					'disabled'      => fake()->boolean(3),
					'min'           => null,
					'max'           => null,
					'step'          => null,
					'minlength'     => 0,
					'maxlength'     => 255,
					'rows'          => null,
					'cols'          => null,
					'wrap'          => null,
					'spellcheck'    => 'false',
					'options'       => null,
					'optgroups'     => null,
					'aria'          => $aria,
					'dataset'       => $dataset,
					'accepts'       => null,
					'selectors'     => $selectors,
					'size'          => $size,
				]);

				$created++;
			}
		}

		$this->command?->info(sprintf(
			'CustomFieldsSeeder: %d registros criados em %s (mínimo alvo: %d; módulos: %d; multiplicador: %d).',
			$created,
			DC::TABLE_CUSTOM_FIELDS,
			$targetTotal,
			$moduleCount,
			$multiplier
		));
	}
}
