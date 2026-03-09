<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, ProjectsConstants as PJC, UsersConstants as UC};
use App\Models\{Lead, Pipeline};
use Carbon\CarbonImmutable as Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LeadSeeder extends Seeder
{
	// ---------- Parâmetros fixos (mocking: não usar env) ----------
	private const SEED            = 20251201;
	private const DEFAULT_COUNT   = 256;    // total objetivo (ajustável via --count)
	private const OPTIONALITY     = 0.65;  // prob. média de preencher campos opcionais
	private const MAX_TAGS        = 3;     // máx. IDs em sources/products/labels

	public function run(): void
	{
		fake()->seed(self::SEED);

		if (!Schema::hasTable(DC::TABLE_LEADS)) {
			$this->command?->warn('Tabela de leads ausente. Abortado.');
			return;
		}

		// Bases (todas opcionais, exceto Pipelines)
		$pipelineIds = Pipeline::query()->pluck('id')->all();
		if (!$pipelineIds) {
			$this->command?->warn('Nenhum pipeline encontrado. Nada a semear em leads.');
			return;
		}

		$stageByPipeline = $this->mapStagesByPipeline();
		$userIds         = Schema::hasTable(DC::TABLE_USERS)       ? DB::table(DC::TABLE_USERS)->pluck('id')->all()       : [];
		$employeeIds     = Schema::hasTable(DC::TABLE_EMPLOYEES)   ? DB::table(DC::TABLE_EMPLOYEES)->pluck('id')->all()   : [];
		$productIds      = Schema::hasTable(DC::TABLE_PROD_SERVS)  ? DB::table(DC::TABLE_PROD_SERVS)->pluck('id')->all()  : [];
		// Tabelas opcionais sem constantes conhecidas: usar nomes canônicos
		$labelIds        = Schema::hasTable('labels')              ? DB::table('labels')->pluck('id')->all()              : [];
		$sourceIds       = Schema::hasTable('sources')             ? DB::table('sources')->pluck('id')->all()             : [];

		$target = (int) ($this->command instanceof \Illuminate\Console\Command && $this->command?->hasOption('count') ? $this->command?->option('count') : self::DEFAULT_COUNT);
		$target = max(1, $target);

		$inserted = 0;
		DB::beginTransaction();
		try {
			// Distribuição simples: percorre pipelines e cria blocos até atingir o alvo
			while ($inserted < $target) {
				foreach ($pipelineIds as $pplId) {
					if ($inserted >= $target) break;

					$batch = fake()->numberBetween(3, 10); // leads por pipeline nesta rodada
					for ($i = 0; $i < $batch && $inserted < $target; $i++) {
						try {
							$now  = Carbon::now();
							$date = fake()->boolean(75)
								? $now->subDays(fake()->numberBetween(0, 120))->toDateString()
								: $now->toDateString();

							// Usuário responsável e caller (opcionais)
							$userId   = $this->maybe() && $userIds     ? Arr::random($userIds)     : null;
							$callerId = $this->maybe(0.40) && $employeeIds ? Arr::random($employeeIds) : null;

							// Stage coerente com o pipeline
							$stageId = null;
							if (!empty($stageByPipeline[$pplId])) {
								$stageId = $this->maybe(0.80) ? Arr::random($stageByPipeline[$pplId]) : null;
							}

							// Campos textuais
							$hasName = $this->maybe(0.85); // name é nullable; geralmente presente
							$name    = $hasName ? fake()->name() : null;

							$email   = $this->maybe(0.80) ? fake()->unique()->safeEmail() : null;
							$phone   = $this->maybe(0.70) ? fake()->cellphoneNumber()     : null;
							$subject = $this->fakeSubject();

							// Listas CSV (IDs existentes quando houver)
							$labelsCsv   = $this->pickCsv($labelIds,   fake()->numberBetween(0, self::MAX_TAGS));
							$productsCsv = $this->pickCsv($productIds, fake()->numberBetween(0, self::MAX_TAGS));
							$sourcesCsv  = $this->pickCsv($sourceIds,  fake()->numberBetween(0, self::MAX_TAGS));

							// Flags
							$isCritical  = $this->maybe(0.15);
							$isConverted = $this->maybe(0.25);

							// Involved base (o boot() adiciona user_id/caller/creator)
							$involved = [
								'users'     => $this->maybe(0.35) && $userIds     ? Arr::random($userIds,   fake()->numberBetween(1, min(3, max(1, count($userIds)))))   : [],
								'employees' => $this->maybe(0.30) && $employeeIds ? Arr::random($employeeIds, fake()->numberBetween(1, min(2, max(1, count($employeeIds))))) : [],
							];
							// Garantir arrays
							foreach (['users', 'employees'] as $k) {
								if (!is_array($involved[$k])) $involved[$k] = $involved[$k] ? [$involved[$k]] : [];
							}

							// Order e notas
							$order = fake()->numberBetween(0, 100);
							$notes = $this->maybe() ? fake()->realText(fake()->numberBetween(60, 180)) : null;

							// Montagem
							$payload = [
								'name'              => $name,
								'email'             => $email,
								'phone'             => $phone,
								'subject'           => $subject,
								UC::COL_USER_ID     => $userId,
								PJC::COL_PPL_ID     => $pplId,
								PJC::COL_STG_ID     => $stageId,
								'sources'           => $sourcesCsv,
								'products'          => $productsCsv,
								'labels'            => $labelsCsv,
								'order'             => $order,
								'notes'             => $notes,
								PJC::COL_CNV        => $isConverted, // cast → boolean
								PJC::COL_CRT        => $isCritical,  // cast → boolean
								'date'              => $date,
								'caller'            => $callerId,
								'involved'          => $involved,     // cast → array(json)
							];
							(new \Symfony\Component\Console\Output\ConsoleOutput)->writeln("Criando Lead {$name} sobre {$subject} endereçado para {$email} / {$phone} no pipeline {$pplId}");
						// Cria via Model (aciona booted::saving para normalizações)
							/** @var Lead $lead */
							$lead = Lead::query()->create($payload);

							// Auditoria (guarded): atribuir depois e salvar
							if (Schema::hasColumn(DC::TABLE_LEADS, DC::COL_TABLE_CREATOR) && $this->maybe(0.35)) {
								$lead->{DC::COL_TABLE_CREATOR} = $userIds ? Arr::random($userIds) : null;
							}
							if (Schema::hasColumn(DC::TABLE_LEADS, DC::COL_TABLE_UPDATER) && $this->maybe(0.25)) {
								$lead->{DC::COL_TABLE_UPDATER} = $userIds ? Arr::random($userIds) : null;
							}
							if ($lead->isDirty()) {
								$lead->save(); // reaciona boot::saving para re-normalizar 'involved'
							}

							$inserted++;
						} catch (\Exception $e) {
							$inserted++;
							Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
							continue;
						}
					}
				}
			}

			DB::commit();
			$this->command?->info("LeadSeeder: {$inserted} leads criados em " . DC::TABLE_LEADS . '.');
		} catch (\Throwable $e) {
			DB::rollBack();
			$this->command?->error('LeadSeeder falhou: ' . $e->getMessage());
			throw $e;
		}
	}

	// ------------------------ Helpers ------------------------

	private function maybe(?float $p = null): bool
	{
		$p = $p ?? self::OPTIONALITY;
		return fake()->boolean((int) round(max(0, min(1, $p)) * 100));
	}

	private function fakeSubject(): string
	{
		$prefix = Arr::random(['Consulta', 'Pedido de orçamento', 'Dúvida', 'Solicitação', 'Proposta', 'Follow-up']);
		$topic  = Arr::random(['implantação', 'suporte', 'licenciamento', 'customização', 'integração', 'treinamento']);
		return "{$prefix} sobre {$topic}";
	}

	/**
	 * Retorna string CSV de IDs (ou null) com até $max itens.
	 */
	private function pickCsv(array $ids, int $max): ?string
	{
		$max = max(0, $max);
		if (!$ids || $max === 0 || !$this->maybe()) return null;

		$n = min($max, count($ids));
		$pick = $n > 1 ? Arr::random($ids, fake()->numberBetween(1, $n)) : [$ids[array_rand($ids)]];
		if (!is_array($pick)) $pick = [$pick];

		return implode(',', array_values(array_unique(array_map('strval', $pick))));
	}

	/**
	 * Mapa pipeline_id => [stage_id, ...]
	 */
	private function mapStagesByPipeline(): array
	{
		$out = [];
		if (!Schema::hasTable(DC::TABLE_LEAD_STAGES)) {
			return $out;
		}

		$rows = DB::table(DC::TABLE_LEAD_STAGES)
			->select('id', PJC::COL_PPL_ID . ' as pipeline_id')
			->get();

		foreach ($rows as $r) {
			if (!$r->pipeline_id || !$r->id) continue;
			$out[$r->pipeline_id][] = $r->id;
		}

		return $out;
	}
}
