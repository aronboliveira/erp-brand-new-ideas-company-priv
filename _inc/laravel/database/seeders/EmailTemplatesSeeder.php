<?php

namespace Database\Seeders;

use App\Config\Constants\{ActivitiesConstants as AC, DatabaseConstants as DC, MessagesConstants as MC};
use App\Enums\AvailableLang;
use App\Enums\EmailTemplateType;
use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Schema};
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutput;

class EmailTemplatesSeeder extends Seeder
{
	private const SEED = 20251215;

	/**
	 * Gera entre 1 e N templates de e-mail por tipo.
	 *
	 * @return void
	 */
	public function run(): void
	{
		fake()->seed(self::SEED);

		$output = new ConsoleOutput();

		if (!Schema::hasTable(DC::TABLE_EMAIL_TEMPLATES)) {
			$this->command?->warn('EmailTemplatesSeeder: Tabela ' . DC::TABLE_EMAIL_TEMPLATES . ' ausente. Seeder abortado.');
			return;
		}

		$types = EmailTemplateType::cases();
		$typesCount = count($types);

		// Fatores de controle para cada tipo
		$langs = array_map(fn($item) => strtolower($item), EmailTemplateType::labels());

		$totalCreated = 0;

		$output->writeln('<info>EmailTemplatesSeeder:</info> Gerando templates de e-mail para ' . $typesCount . ' tipos.');

		foreach ($types as $typeEnum) {
			$typeValue = $typeEnum->value;
			$langsAvailable = empty($langs) ? [DC::DEFAULT_LANG] : $langs;

			$typeCount = fake()->numberBetween(1, count(AvailableLang::cases()) * 0.2 + 1);

			for ($i = 0; $i < $typeCount; $i++) {
				foreach ($langsAvailable as $lang) {
					$notificationId = $this->getRandomNotificationId();

					// Gerar atributos do template
					$attributes = $this->generateTemplateAttributes($typeEnum, $lang, $notificationId);

					// Criação do template de e-mail
					(new \Symfony\Component\Console\Output\ConsoleOutput())->writeln(
						"<comment>Criando EmailTemplate com notificação {$notificationId} do tipo '{$typeValue}' para o idioma '{$lang}'...</comment>"
					);
					$template = EmailTemplate::create($attributes);

					// Exibindo no console o progresso da criação
					$output->writeln(sprintf(
						'EmailTemplate criado: "%s" (%s) com tipo "%s" para o idioma "%s".',
						Str::limit($template->title, 30),
						$template->slug,
						$typeValue,
						$lang
					));

					$totalCreated++;
				}
			}
		}

		$this->command?->info("EmailTemplatesSeeder concluído: $totalCreated templates criados.");
	}

	/**
	 * Gera os atributos necessários para um template de e-mail.
	 *
	 * @param EmailTemplateType $type
	 * @param string $lang
	 * @param string|null $notificationId
	 * @return array
	 */
	private function generateTemplateAttributes(EmailTemplateType $type, string $lang, ?string $notificationId): array
	{
		$title = fake()->sentence(3);
		$slug = Str::slug($title);
		$description = fake()->boolean(70) ? fake()->sentence(10) : null;
		$from = fake()->email();
		$categories = fake()->words(3);
		$variables = $this->generateVariables();
		$settings = $this->generateSettings();
		$tags = fake()->words(3);
		$platformsAvailable = fake()->boolean(75) ? ['web', 'mobile', 'desktop'] : (fake()->boolean(50) ? ['web'] : ['mobile']);
		$langs = [];
		foreach (array_column(AvailableLang::cases(), 'value') as $lang) {
			$isIncluded = fake()->boolean(50);
			if ($isIncluded && !in_array($lang, $langs, true))
				$langs[] = $lang;
		}
		return [
			'type'          => $type->value,
			'title'         => $title,
			'slug'          => $slug,
			'description'   => $description,
			'from'          => $from,
			'notification'  => $notificationId,
			'categories'    => $categories,
			'variables'     => $variables,
			'settings'      => $settings,
			'tags'          => $tags,
			DC::COL_PLT_AV  => $platformsAvailable,
			MC::COL_AV_LG     => $langs, // Suporte a um único idioma por vez
			AC::COL_AV_FROM => now(),
			AC::COL_DSB     => fake()->boolean(95) ? false : true,
		];
	}

	/**
	 * Gera um conjunto de variáveis aleatórias para o template.
	 *
	 * @return array
	 */
	private function generateVariables(): array
	{
		return [
			'user_name' => 'string',
			'user_email' => 'email',
			'discount_code' => 'string',
			'order_number' => 'string',
			'item_name' => 'string',
		];
	}

	/**
	 * Gera um conjunto de configurações aleatórias para o template.
	 *
	 * @return array
	 */
	private function generateSettings(): array
	{
		return [
			'priority' => fake()->randomElement(['low', 'normal', 'high']),
			'read_receipt' => fake()->boolean(20),
		];
	}

	/**
	 * Retorna um ID aleatório de notificação, se disponível.
	 *
	 * @return string|null
	 */
	private function getRandomNotificationId(): ?string
	{
		// Busca um ID aleatório de notificação da tabela de templates de notificação
		$notificationIds = DB::table(DC::TABLE_NOTIFICATION_TEMPLATES)->pluck('id')->all();
		return !empty($notificationIds) ? fake()->randomElement($notificationIds) : null;
	}
}
