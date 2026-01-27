<?php

namespace Database\Seeders;

use App\Enums\CaseStatus;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

use App\Config\Constants\{
	DatabaseConstants as DC,
	ActivitiesConstants as AC
};
use App\Models\BugStatus as Bst;

final class BugStatusSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function () {
			$systemUserId = $this->ensureSystemUser();

			$rows = [
				['title' => 'Aberto', 'description' => 'O relato de bug foi criado e está aguardando triagem.'],
				['title' => 'Em andamento', 'description' => 'O relato de bug está sendo analisado ou corrigido.'],
				['title' => 'Em revisão', 'description' => 'A correção do relato de bug está em fase de revisão ou testes.'],
				['title' => 'Bloqueado', 'description' => 'O relato de bug está bloqueado por algum motivo e não pode avançar no momento.'],
				['title' => 'Resolvido', 'description' => 'O relato de bug foi corrigido e aguarda validação final.'],
				['title' => 'Fechado', 'description' => 'O relato de bug foi corrigido e validado, estando oficialmente encerrado.'],
				['title' => 'Não será corrigido', 'description' => 'O relato de bug não será corrigido devido a decisões técnicas ou de negócio.'],
				['title' => 'Duplicado', 'description' => 'O relato de bug é um duplicado de outro relato já existente.'],
				['title' => 'Rejeitado', 'description' => 'O relato de bug foi analisado e rejeitado por não ser considerado um problema.']
			];

			foreach (CaseStatus::cases() as $status) {
				$title = is_callable([$status, 'label'])
					? $status->label()
					: fake(fake()->boolean(80) ? 'pt_BR' : (fake()->boolean(50) ? 'en_US' : 'es_ES'))
					->words(fake()->boolean(50) ? 1 : 2, true);

				if (!in_array($title, array_column($rows, 'title'))) {
					$rows[] = [
						'title' => $title,
						'description' => fake(fake()->boolean(80) ? 'pt_BR' : (fake()->boolean(50) ? 'en_US' : 'es_ES'))->sentence(),
					];
				}
			}

			while (log(count($rows), 2) % 1 !== 0 || count($rows) < 32) {
				$titleCandidate = fake(fake()->boolean(80) ? 'pt_BR' : (fake()->boolean(50) ? 'en_US' : 'es_ES'))
					->words(fake()->boolean(50) ? 1 : 2, true);

				if (!in_array($titleCandidate, array_column($rows, 'title'))) {
					$rows[] = [
						'title' => $titleCandidate,
						'description' => fake(fake()->boolean(80) ? 'pt_BR' : (fake()->boolean(50) ? 'en_US' : 'es_ES'))->sentence(),
					];
				}
			}

			$order = 0;
			foreach ($rows as $r) {
				try {
					$title = $r['title'];
					if (Bst::where(AC::COL_TT, $title)->exists()) {
						do {
							$title = fake(fake()->boolean(80) ? 'pt_BR' : (fake()->boolean(50) ? 'en_US' : 'es_ES'))
								->words(fake()->boolean(50) ? 1 : 2, true);
						} while (Bst::where(AC::COL_TT, $title)->exists());
					}

					(new \Symfony\Component\Console\Output\ConsoleOutput())
						->writeln("Criando Status de Bug [{$order}]: {$title}");

					$bs = new Bst();
					$bs->{AC::COL_OD} = $order;
					$bs->{AC::COL_TT} = $title;
					$bs->description = $r['description'] ?? null;
					$bs->{DC::COL_TABLE_CREATOR} = $systemUserId;
					$bs->setAttribute(DC::COL_TABLE_UPDATER, null);
					$bs->save();

					$order++;
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}
		}, 3);
	}
}
