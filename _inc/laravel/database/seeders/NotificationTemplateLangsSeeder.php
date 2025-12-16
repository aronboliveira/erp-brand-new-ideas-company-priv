<?php

namespace Database\Seeders;

use App\Config\Constants\{
	DatabaseConstants as DC,
	MessagesConstants as MC,
	NotificationsConstants as NC
};
use App\Enums\AvailableLang;
use App\Models\NotificationTemplateLang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class NotificationTemplateLangsSeeder extends Seeder
{
	private const SEED = 20251211;

	public function run(): void
	{
		fake()->seed(self::SEED);

		// Sanidade das tabelas
		if (!Schema::hasTable(DC::TABLE_NOTIFICATION_TEMPLATES)) {
			$this->command?->warn('NotificationTemplateLangsSeeder: tabela de notification_templates ausente. Seeder abortado.');
			return;
		}

		if (!Schema::hasTable(DC::TABLE_NOTIFICATION_TEMPLATE_LANGS)) {
			$this->command?->warn('NotificationTemplateLangsSeeder: tabela de notification_template_langs ausente. Seeder abortado.');
			return;
		}

		if (!Schema::hasTable(DC::TABLE_USERS)) {
			$this->command?->warn('NotificationTemplateLangsSeeder: tabela de users ausente. Traduções serão criadas sem MC::COL_TRL_ID.');
		}

		// Templates existentes (leitura via DB::table)
		$templates = DB::table(DC::TABLE_NOTIFICATION_TEMPLATES)
			->select('id', 'name', 'type')
			->get();

		if ($templates->isEmpty()) {
			$this->command?->warn('NotificationTemplateLangsSeeder: nenhum template encontrado em ' . DC::TABLE_NOTIFICATION_TEMPLATES . '.');
			return;
		}

		// Usuários possíveis para MC::COL_TRL_ID (tradutor)
		$userIds = [];
		if (Schema::hasTable(DC::TABLE_USERS)) {
			$userIds = DB::table(DC::TABLE_USERS)
				->select('id')
				->pluck('id')
				->all();
		}

		// Enum de idiomas
		$langCases = AvailableLang::cases();
		if (empty($langCases)) {
			$this->command?->warn('NotificationTemplateLangsSeeder: enum AvailableLang sem casos. Nada a semear.');
			return;
		}

		// Carrega combinações já existentes (template_id + lang) para não duplicar
		$existing = DB::table(DC::TABLE_NOTIFICATION_TEMPLATE_LANGS)
			->select(NC::COL_TEMPL_PR . ' as template_id', NC::COL_TEMPL_LG . ' as lang')
			->get();

		$existingMap = [];
		foreach ($existing as $row) {
			$tId = (string) $row->template_id;
			$lg  = (string) $row->lang;
			if (!isset($existingMap[$tId])) {
				$existingMap[$tId] = [];
			}
			$existingMap[$tId][$lg] = true;
		}

		// Total de combinações ainda disponíveis (sem romper unique(template_id, lang))
		$totalAvailableCombos = 0;
		$langCount            = count($langCases);

		foreach ($templates as $tpl) {
			$tId          = (string) $tpl->id;
			$alreadyForT  = isset($existingMap[$tId]) ? count($existingMap[$tId]) : 0;
			$availableForT = max(0, $langCount - $alreadyForT);
			$totalAvailableCombos += $availableForT;
		}

		if ($totalAvailableCombos === 0) {
			$this->command?->info('NotificationTemplateLangsSeeder: todas as combinações template+lang já estão preenchidas. Nada a fazer.');
			return;
		}

		// N base e alvo 64 x N
		$templateCount = $templates->count();

		$multiplier = $this->command instanceof \Illuminate\Console\Command && $this->command->hasOption('count')
			? max(1, (int) $this->command->option('count'))
			: max(1, $templateCount);

		$targetTotal = 64 * $multiplier;
		// Não podemos criar mais que as combinações únicas disponíveis
		$targetTotal = min($targetTotal, $totalAvailableCombos);

		$faker     = fake();
		$created   = 0;
		$remaining = $targetTotal;

		// Para cálculo do "último template"
		$templatesArray = $templates->all();
		$totalTpl       = count($templatesArray);

		foreach ($templatesArray as $index => $tpl) {
			if ($remaining <= 0) {
				break;
			}

			$templateId   = (string) $tpl->id;
			$templateName = (string) ($tpl->name ?? 'Template');
			$templateType = (string) ($tpl->type ?? 'unknown');

			$existingLangsForTemplate = isset($existingMap[$templateId])
				? array_keys($existingMap[$templateId])
				: [];

			$availableForTemplate = max(0, $langCount - count($existingLangsForTemplate));
			if ($availableForTemplate === 0) {
				continue;
			}

			$isLastTemplate = ($index === $totalTpl - 1);

			// Número de idiomas a criar para este template:
			// - se for o último template, tenta preencher tudo que ainda falta;
			// - senão, valor aleatório entre 1 e min(available, remaining).
			if ($isLastTemplate) {
				$toCreateForTemplate = min($availableForTemplate, $remaining);
			} else {
				$toCreateForTemplate = $faker->numberBetween(
					1,
					min($availableForTemplate, $remaining)
				);
			}

			if ($toCreateForTemplate <= 0) {
				continue;
			}

			// Para evitar repetições na mesma execução
			$usedLangsThisRun = [];

			for ($i = 0; $i < $toCreateForTemplate && $remaining > 0; $i++) {
				// Escolha de idioma com do/while e exists() para garantir uniqueness
				$attempts = 0;
				$langEnum = null;

				do {
					$attempts++;
					if ($attempts > ($langCount * 4)) {
						// proteção contra loop excessivo caso algo esteja inconsistente
						continue 2; // vai para o próximo template
					}

					/** @var \App\Enums\AvailableLang $candidate */
					$candidate = Arr::random($langCases);
					$candidateValue = $candidate->value;

					$alreadyInMemory =
						in_array($candidateValue, $existingLangsForTemplate, true)
						|| isset($usedLangsThisRun[$candidateValue]);

					if ($alreadyInMemory) {
						$exists = true;
					} else {
						// Verificação real no banco (conforme requisito do do/while com exists())
						$exists = DB::table(DC::TABLE_NOTIFICATION_TEMPLATE_LANGS)
							->where(NC::COL_TEMPL_PR, $templateId)
							->where(NC::COL_TEMPL_LG, $candidateValue)
							->exists();
					}

					if (!$exists) {
						$langEnum = $candidate;
						break;
					}
				} while (true);

				if (!$langEnum instanceof AvailableLang) {
					continue;
				}

				$langValue = $langEnum->value;
				$usedLangsThisRun[$langValue] = true;

				// Atualiza mapa em memória para este template
				if (!isset($existingMap[$templateId])) {
					$existingMap[$templateId] = [];
				}
				$existingMap[$templateId][$langValue] = true;

				// Montagem de conteúdo/variáveis/metadata
				$langLabels = AvailableLang::labels();
				$langLabel  = $langLabels[$langValue] ?? $langValue;

				$contentLines = [
					"Olá {{user_name}},",
					"",
					sprintf(
						"Esta é uma mensagem de teste para o template \"%s\" (%s) no idioma %s.",
						$templateName,
						$templateType,
						$langLabel
					),
					"",
					"Use {{action_url}} para acessar mais detalhes ou {{support_email}} para entrar em contato.",
				];
				$content = implode("\n", $contentLines);

				// Variáveis (mantendo shape; sem array_filter)
				$variables = [
					'user_name'    => '{{user_name}}',
					'action_url'   => '{{action_url}}',
					'support_email' => '{{support_email}}',
					'company_name' => '{{company_name}}',
					// não remover nulls: se precisar adicionar algo condicional no futuro, manter como está
				];

				// Metadata simples para debug/identificação
				$metadata = [
					'generated_by' => 'NotificationTemplateLangsSeeder',
					'lang'         => $langValue,
					'template_id'  => $templateId,
					'seed'         => self::SEED,
					'created_at'   => Carbon::now()->toIso8601String(),
				];

				// Tradutor (nome) e tradutor_id opcionais
				$translatorName = $faker->boolean(60) ? $faker->name() : null;
				$translatorId   = null;
				if (!empty($userIds) && $faker->boolean(50)) {
					$translatorId = Arr::random($userIds);
				}
				(new \Symfony\Component\Console\Output\ConsoleOutput())->writeln("Generating notification template lang {$langValue} for {$templateId} {$templateName} ({$templateType})");
				// Criação via Model (respeita casts + booted)
				NotificationTemplateLang::query()->create([
					NC::COL_TEMPL_PR => $templateId,
					NC::COL_TEMPL_LG => $langValue,
					NC::COL_TEMPL_CT => $content,
					NC::COL_TEMPL_VARS => $variables,
					'translator'      => $translatorName,
					MC::COL_TRL_ID    => $translatorId,
					'metadata'        => $metadata,
				]);

				$created++;
				$remaining--;

				if ($remaining <= 0) {
					break;
				}
			}
		}

		$this->command?->info(sprintf(
			'NotificationTemplateLangsSeeder: %d traduções criadas em %s (alvo 64 x %d, limitado às combinações disponíveis: %d).',
			$created,
			DC::TABLE_NOTIFICATION_TEMPLATE_LANGS,
			$multiplier,
			$totalAvailableCombos
		));
	}
}
