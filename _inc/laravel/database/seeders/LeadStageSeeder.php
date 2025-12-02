<?php

namespace Database\Seeders;

use App\Config\Constants\{
	ActivitiesConstants as AC,
	DatabaseConstants as DC,
	ProjectsConstants as PJC
};
use App\Models\{LeadStage, Pipeline};
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LeadStageSeeder extends Seeder
{
	/**
	 * Parâmetros fixos (NÃO usar env em seeders de mocking)
	 */
	private const OPTIONALITY            = 0.65; // probabilidade média de preencher opcionais
	private const PER_PIPELINE_EXTRA_MIN = 0;    // estágios extras aleatórios por pipeline (mín.)
	private const PER_PIPELINE_EXTRA_MAX = 3;    // estágios extras aleatórios por pipeline (máx.)
	private const SEED                   = 20251201;

	public function run(): void
	{
		// Reprodutibilidade
		fake()->seed(self::SEED);

		// Sanidade de tabela necessária
		if (!Schema::hasTable(DC::TABLE_LEAD_STAGES)) {
			$this->command?->warn('Tabela de estágios (lead_stages) ausente. Seeder abortado.');
			return;
		}

		// Pipelines existentes (usamos Model para evitar suposições de nome de tabela)
		$pipelineIds = Pipeline::query()->pluck('id')->all();
		if (!$pipelineIds) {
			$this->command?->warn('Nenhum pipeline encontrado. Nada a semear em lead_stages.');
			return;
		}

		// Usuários (opcional) para auditoria
		$userIds = Schema::hasTable(DC::TABLE_USERS)
			? DB::table(DC::TABLE_USERS)->pluck('id')->all()
			: [];

		// Helpers
		$maybe = function (?float $p = null): bool {
			$p = $p ?? self::OPTIONALITY;
			return fake()->boolean((int) round(max(0, min(1, $p)) * 100));
		};

		$intOrNull = function (int $min, int $max) use ($maybe): ?int {
			return $maybe() ? fake()->numberBetween($min, $max) : null;
		};

		$boolOrNull = function (?float $pNull = 0.30): ?bool {
			// ~30% null, senão true/false
			if (fake()->boolean((int) round(($pNull ?? 0.30) * 100))) {
				return null;
			}
			return fake()->boolean();
		};

		$notesOrNull = function (?float $p = null): ?string {
			return fake()->boolean((int) round(($p ?? self::OPTIONALITY) * 100))
				? fake()->realText(fake()->numberBetween(40, 160))
				: null;
		};

		// Conjunto fixo (idempotente) de estágios "clássicos"
		$fixtures = [
			['nm' => 'Novo',         'order' => 0,  'chance' => 0,   'crit' => null],
			['nm' => 'Qualificação', 'order' => 10, 'chance' => 20,  'crit' => false],
			['nm' => 'Proposta',     'order' => 20, 'chance' => 50,  'crit' => false],
			['nm' => 'Negociação',   'order' => 30, 'chance' => 65,  'crit' => null],
			['nm' => 'Fechamento',   'order' => 40, 'chance' => 90,  'crit' => true],
			['nm' => 'Perdido',      'order' => 99, 'chance' => 0,   'crit' => false],
		];

		DB::beginTransaction();
		try {
			foreach ($pipelineIds as $pipelineId) {
				// 1) Fixtures idempotentes por pipeline
				foreach ($fixtures as $fx) {
					$payload = [
						PJC::COL_STG_NM  => $fx['nm'],
						PJC::COL_PPL_ID  => $pipelineId,
						AC::COL_OD       => $fx['order'],
						'notes'          => $notesOrNull(),
						PJC::COL_EST_CC  => $this->coerceIntOrKeepNull($intOrNull(0, 100), $fx['chance']),
						PJC::COL_CRT     => $fx['crit'],
					];

					/** @var LeadStage $stage */
					$stage = LeadStage::query()->updateOrCreate(
						[PJC::COL_PPL_ID => $pipelineId, PJC::COL_STG_NM => $fx['nm']],
						$payload
					);

					// Auditoria opcional
					if (Schema::hasColumn(DC::TABLE_LEAD_STAGES, DC::COL_TABLE_CREATOR) && $this->shouldFillAudit()) {
						$stage->{DC::COL_TABLE_CREATOR} = $userIds ? Arr::random($userIds) : null;
					}
					if (Schema::hasColumn(DC::TABLE_LEAD_STAGES, DC::COL_TABLE_UPDATER) && $this->shouldFillAudit()) {
						$stage->{DC::COL_TABLE_UPDATER} = $userIds ? Arr::random($userIds) : null;
					}
					if ($stage->isDirty()) {
						$stage->save();
					}
				}

				// 2) Extras aleatórios por pipeline (0..N) com combinações diversas
				$extras = fake()->numberBetween(self::PER_PIPELINE_EXTRA_MIN, self::PER_PIPELINE_EXTRA_MAX);
				$baseOrder = 50;

				for ($i = 0; $i < $extras; $i++) {
					// Gera um nome razoável e garante unicidade por pipeline
					$name = $this->uniqueStageName($pipelineId);

					$payload = [
						PJC::COL_STG_NM  => $name,
						PJC::COL_PPL_ID  => $pipelineId,
						AC::COL_OD       => $baseOrder + ($i * 5) + fake()->numberBetween(0, 4),
						'notes'          => $notesOrNull(),
						PJC::COL_EST_CC  => $intOrNull(0, 100),   // às vezes null
						PJC::COL_CRT     => $boolOrNull(),        // às vezes null/true/false
					];

					$stage = LeadStage::query()->create($payload);

					// Auditoria opcional (atribuição direta para não violar guarded)
					if (Schema::hasColumn(DC::TABLE_LEAD_STAGES, DC::COL_TABLE_CREATOR) && $this->shouldFillAudit()) {
						$stage->{DC::COL_TABLE_CREATOR} = $userIds ? Arr::random($userIds) : null;
					}
					if (Schema::hasColumn(DC::TABLE_LEAD_STAGES, DC::COL_TABLE_UPDATER) && $this->shouldFillAudit()) {
						$stage->{DC::COL_TABLE_UPDATER} = $userIds ? Arr::random($userIds) : null;
					}
					if ($stage->isDirty()) {
						$stage->save();
					}
				}
			}

			DB::commit();
			$this->command?->info('LeadStageSeeder: estágios criados/atualizados por pipeline (fixtures + extras).');
		} catch (\Throwable $e) {
			DB::rollBack();
			$this->command?->error('LeadStageSeeder falhou: ' . $e->getMessage());
			throw $e;
		}
	}

	/**
	 * Em ~35% dos casos, preenchemos colunas de auditoria.
	 */
	private function shouldFillAudit(): bool
	{
		return fake()->boolean(35);
	}

	/**
	 * Mantém valor inteiro sugerido, priorizando um fallback conhecido (ex.: dos fixtures).
	 */
	private function coerceIntOrKeepNull(?int $random, ?int $fallback): ?int
	{
		if ($random === null && $fallback !== null) {
			return (int) $fallback;
		}
		return $random === null ? null : max(0, min(100, (int) $random));
		// clamp defensivo de 0..100
	}

	/**
	 * Gera um nome de estágio único por pipeline.
	 */
	private function uniqueStageName(string $pipelineId): string
	{
		$pool = [
			'Descoberta',
			'Análise',
			'Apresentação',
			'Follow-up',
			'Due Diligence',
			'Revisão Técnica',
			'Validação',
			'Prioritário',
			'Escopo Fechado',
			'Alinhamento',
			'Revisão Comercial',
		];

		for ($i = 0; $i < 10; $i++) {
			$base = Arr::random($pool);
			$name = $base . ' ' . fake()->randomElement(['', '#' . fake()->numberBetween(1, 9), Str::upper(Str::random(2))]);
			$name = trim($name);

			$exists = LeadStage::query()
				->where(PJC::COL_PPL_ID, $pipelineId)
				->where(PJC::COL_STG_NM, $name)
				->exists();

			if (!$exists) return $name;
		}

		// Fallback hard-único
		return 'Stage ' . Str::upper(Str::random(6));
	}
}
