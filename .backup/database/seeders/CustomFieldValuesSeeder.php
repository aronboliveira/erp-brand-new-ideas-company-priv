<?php

namespace Database\Seeders;

use App\Config\Constants\DatabaseConstants as DC;
use App\Enums\{AppModuleType, FieldType};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Support\{Arr, Str};
use Symfony\Component\Console\Output\ConsoleOutput;
use Carbon\Carbon;

class CustomFieldValuesSeeder extends Seeder
{
	private const SEED = 20251213;

	/** @var array<string,string> */
	private const MODULE_TABLE_MAP = [
		// Módulos de negócio
		'financial' => DC::TABLE_BILLS,
		'sales'     => DC::TABLE_ORDERS,
		'crm'       => DC::TABLE_LEADS,
		'hrm'       => DC::TABLE_EMPLOYEES,
		'projects'  => DC::TABLE_PROJECTS,
		'inventory' => DC::TABLE_PROD_SERVS,
		'support'   => DC::TABLE_SUPPORTS,

		// Técnicos
		'database'       => DC::TABLE_DOCS,
		'infrastructure' => DC::TABLE_WHS,

		// Entidades específicas
		'user'     => DC::TABLE_USERS,
		'customer' => DC::TABLE_CUSTOMERS,
		'vendor'   => DC::TABLE_VENDORS,
		'product'  => DC::TABLE_PRODUCTS,
		'invoice'  => DC::TABLE_INVS,
		'bill'     => DC::TABLE_BILLS,
		'account'  => DC::TABLE_BANK_ACC,
	];

	public function run(): void
	{
		fake()->seed(self::SEED);

		$output = new ConsoleOutput();

		if (!Schema::hasTable(DC::TABLE_CFV)) {
			$this->command?->warn(
				'CustomFieldValuesSeeder: tabela ' . DC::TABLE_CFV . ' ausente. Seeder abortado.'
			);
			return;
		}

		if (!Schema::hasTable(DC::TABLE_CUSTOM_FIELDS)) {
			$this->command?->warn(
				'CustomFieldValuesSeeder: tabela ' . DC::TABLE_CUSTOM_FIELDS . ' ausente. Seeder abortado.'
			);
			return;
		}

		$fields = DB::table(DC::TABLE_CUSTOM_FIELDS)
			->select(['id', 'name', 'type', 'module', 'required', 'options'])
			->get();

		if ($fields->isEmpty()) {
			$this->command?->warn(
				'CustomFieldValuesSeeder: nenhuma definição em ' . DC::TABLE_CUSTOM_FIELDS . '.'
			);
			return;
		}

		$totalInserted = 0;
		$totalSkipped  = 0;

		foreach ($fields as $field) {
			// Módulo normalizado
			$moduleRaw = $field->module ?? '';
			$module = is_string($moduleRaw) ? trim(strtolower($moduleRaw)) : '';

			if ($module === '') {
				$this->command?->warn(sprintf(
					'CFV: campo "%s" (%s) sem módulo definido. Ignorando.',
					$field->name ?? ('#' . $field->id),
					$field->id
				));
				$totalSkipped++;
				continue;
			}

			$baseTable = self::MODULE_TABLE_MAP[$module] ?? null;
			if (!$baseTable) {
				$this->command?->warn(sprintf(
					'CFV: módulo "%s" do campo "%s" (%s) não possui tabela base mapeada. Ignorando.',
					$module,
					$field->name ?? ('#' . $field->id),
					$field->id
				));
				$totalSkipped++;
				continue;
			}

			if (!Schema::hasTable($baseTable)) {
				$this->command?->warn(sprintf(
					'CFV: tabela base "%s" para módulo "%s" não existe. Campo "%s" (%s) ignorado.',
					$baseTable,
					$module,
					$field->name ?? ('#' . $field->id),
					$field->id
				));
				$totalSkipped++;
				continue;
			}

			// Um único registro de base para este campo
			$recordId = DB::table($baseTable)
				->inRandomOrder()
				->value('id');

			if (!$recordId) {
				$this->command?->warn(sprintf(
					'CFV: nenhum registro em "%s" para módulo "%s". Campo "%s" (%s) ignorado.',
					$baseTable,
					$module,
					$field->name ?? ('#' . $field->id),
					$field->id
				));
				$totalSkipped++;
				continue;
			}

			// Respeitar unique(record_id, field_id)
			$exists = DB::table(DC::TABLE_CFV)
				->where(DC::COL_RCD_ID, $recordId)
				->where(DC::COL_FLD_ID, $field->id)
				->exists();

			if ($exists) {
				$this->command?->line(sprintf(
					'CFV: já existe valor para record=%s[%s], field=%s (%s). Pulando.',
					$baseTable,
					Str::limit((string) $recordId, 8, '…'),
					$field->name ?? ('#' . $field->id),
					$field->id
				));
				$totalSkipped++;
				continue;
			}

			$fieldTypeEnum = FieldType::normalize($field->type ?? null);

			[$value, $checked] = $this->generateValueForField(
				$fieldTypeEnum,
				$field,
				(string) $module,
				(string) $recordId
			);

			// LOG: descrição da variação ANTES de inserir
			$output->writeln(sprintf(
				'CFV: record=%s[%s], field=%s(type=%s) => value="%s"%s',
				$baseTable,
				Str::limit((string) $recordId, 8, '…'),
				$field->name ?? ('#' . $field->id),
				$fieldTypeEnum->value,
				Str::limit((string) $value, 30, '…'),
				$checked === null ? '' : ', checked=' . ($checked ? '1' : '0')
			));

			DB::table(DC::TABLE_CFV)->insert([
				'id'             => (string) Str::uuid(),
				DC::COL_RCD_ID   => $recordId,
				DC::COL_FLD_ID   => $field->id,
				'value'          => $value,
				'checked'        => $checked,
				DC::COL_C_AT     => now(),
				DC::COL_U_AT     => now(),
			]);

			$totalInserted++;
		}

		$this->command?->info(sprintf(
			'CustomFieldValuesSeeder: %d valores criados em %s, %d campos pulados, total de campos: %d.',
			$totalInserted,
			DC::TABLE_CFV,
			$totalSkipped,
			$fields->count()
		));
	}

	/**
	 * Gera [value, checked] coerentes com o FieldType e com o campo.
	 *
	 * @param FieldType   $type
	 * @param \stdClass   $field
	 * @param string      $module
	 * @param string      $recordId
	 *
	 * @return array{0:string|null,1:bool|null}
	 */
	private function generateValueForField(
		FieldType $type,
		\stdClass $field,
		string $module,
		string $recordId
	): array {
		$checked = null;
		$value   = null;

		$options = [];
		if (isset($field->options) && $field->options !== null) {
			$decoded = json_decode((string) $field->options, true);
			if (is_array($decoded)) {
				$options = $decoded;
			}
		}

		$pickOptionValue = function () use ($options): ?string {
			if ($options === []) {
				return null;
			}
			$opt = Arr::random($options);
			if (is_array($opt)) {
				$val = $opt['value'] ?? ($opt['text'] ?? null);
				return $val !== null ? (string) $val : null;
			}
			if (is_scalar($opt)) {
				return (string) $opt;
			}
			return null;
		};

		switch ($type) {
			case FieldType::Text:
			case FieldType::Search:
				$value = fake()->sentence(6);
				break;

			case FieldType::Textarea:
				$value = fake()->paragraphs(fake()->numberBetween(1, 3), true);
				break;

			case FieldType::Email:
				$value = fake()->safeEmail();
				break;

			case FieldType::Tel:
				$value = fake()->numerify('+55 (##) ####-####');
				break;

			case FieldType::Url:
				$value = fake()->url();
				break;

			case FieldType::Number:
			case FieldType::Range:
				$value = (string) fake()->randomFloat(2, 0, 100000);
				break;

			case FieldType::Date:
				$value = fake()->date('Y-m-d');
				break;

			case FieldType::Time:
				$value = fake()->time('H:i');
				break;

			case FieldType::DateTimeLocal:
				$dt    = Carbon::now()->subDays(fake()->numberBetween(0, 365));
				$value = $dt->format('Y-m-d\TH:i');
				break;

			case FieldType::Month:
				$dt    = Carbon::now()->subMonths(fake()->numberBetween(0, 36));
				$value = $dt->format('Y-m');
				break;

			case FieldType::Week:
				$dt    = Carbon::now()->subWeeks(fake()->numberBetween(0, 52));
				$value = $dt->format('Y-\WW');
				break;

			case FieldType::Select:
				$value = $pickOptionValue() ?? Str::slug($module . '_opt_' . fake()->word(), '_');
				break;

			case FieldType::RadioGroup:
				$checked = fake()->boolean(70);
				$value   = $checked ? ($pickOptionValue() ?? 'on') : 'off';
				break;

			case FieldType::Checkbox:
				$checked = fake()->boolean(60);
				$value   = $checked ? 'on' : 'off';
				break;

			case FieldType::Color:
				$value = sprintf('#%06X', fake()->numberBetween(0, 0xFFFFFF));
				break;

			case FieldType::File:
				$ext   = fake()->randomElement(['pdf', 'docx', 'xlsx', 'png', 'jpg', 'zip']);
				$value = sprintf(
					'%s-%s.%s',
					$module,
					Str::substr($recordId, 0, 8),
					$ext
				);
				break;

			case FieldType::Password:
				$value = fake()->password(10, 20);
				break;

			default:
				$value = fake()->word();
				break;
		}

		if ($value !== null) {
			$value = (string) Str::limit($value, 254, '');
		}

		if (!in_array($type, [FieldType::Checkbox, FieldType::RadioGroup], true)) {
			$checked = null;
		}

		return [$value, $checked];
	}
}
