<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Models\{Contract, ContractType};
use App\Traits\EnsuresSystemUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class ContractSeeder extends Seeder
{
	use EnsuresSystemUser;

	public function run(): void
	{
		DB::transaction(function (): void {
			$faker        = \Faker\Factory::create('pt_BR');
			$systemUserId = $this->ensureSystemUser();

			$tz  = 'America/Sao_Paulo';
			$now = CarbonImmutable::now($tz);

			// Tipos (FK) que pretendemos usar (só aplicamos os que existirem)
			$typeNames = [
				'Prestação de Serviço',
				'Outsourcing',
				'Funcionário PJ (Pessoa Jurídica)',
				'Sócio',
				'Venda de Insumos',
			];

			/** @var array<string,string> $typeIdByName */
			$typeIdByName = ContractType::query()
				->whereIn('name', $typeNames)
				->pluck('id', 'name')
				->all();

			// -- Helpers -----------------------------------------------------

			$genCN = static function (): string {
				return 'CTR-' . strtoupper(Str::random(8));
			};

			$makeUniqueCN = static function () use ($genCN): string {
				$tries = 0;
				do {
					$cn = $genCN();
					$exists = Contract::query()->where(PJC::COL_CN, $cn)->exists();
				} while ($exists && ++$tries < 25);
				return $cn;
			};

			$randCpf = static function (): string {
				$d = fn() => (string) random_int(0, 9);
				$digits = $d() . $d() . $d() . $d() . $d() . $d() . $d() . $d() . $d() . $d() . $d();
				// formata 000.000.000-00
				return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $digits);
			};

			$randCnpj = static function (): string {
				$d = fn() => (string) random_int(0, 9);
				$digits = $d() . $d() . $d() . $d() . $d() . $d() . $d() . $d() . $d() . $d() . $d() . $d() . $d() . $d();
				// formata 00.000.000/0000-00
				return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $digits);
			};

			$randIdf = static function () use ($randCpf, $randCnpj): string {
				return random_int(0, 1) ? $randCpf() : $randCnpj();
			};

			$fullAddress = static function (\Faker\Generator $f): string {
				return $f->streetAddress . ', ' . $f->city . ' - ' . $f->stateAbbr . ', ' . $f->postcode;
			};

			$pickValueForType = static function (?ContractType $type, float $fallbackMin, float $fallbackMax): float {
				$minV = $type?->{BC::COL_MIN_V};
				$maxV = $type?->{BC::COL_MAX_V};

				$low  = is_numeric($minV) ? max(0.0, (float)$minV) : $fallbackMin;
				$high = (is_numeric($maxV) && (float)$maxV > 0) ? (float)$maxV : $fallbackMax;

				if ($low > $high) [$low, $high] = [$high, $low];

				$picked = $low;
				if ($high > $low) {
					$picked = $low + (mt_rand(0, 1000) / 1000) * ($high - $low);
				}
				return round($picked, 2);
			};

			$pickEndDateForType = static function (
				CarbonImmutable $start,
				?ContractType $type,
				int $fallbackMinM,
				int $fallbackMaxM,
				string $tz
			): CarbonImmutable {
				$minM = $type?->{BC::COL_MIN_M};
				$maxM = $type?->{BC::COL_MAX_M};

				$lowM  = is_numeric($minM) ? max(0, (int)$minM) : $fallbackMinM;
				$highM = is_numeric($maxM) ? max($lowM, (int)$maxM) : $fallbackMaxM;

				if ($lowM > $highM) [$lowM, $highM] = [$highM, $lowM];

				$months = ($highM > $lowM) ? random_int($lowM, $highM) : $lowM;

				return $start->addMonthsNoOverflow($months)->timezone($tz);
			};

			$freqOptions = ['once', 'variable', 'hourly', 'biweekly', 'weekly', 'semimonthly', 'semestral', 'monthly', 'annual'];
			$statusOptions = ['draft', 'pending', 'active', 'suspended', 'completed', 'cancelled'];

			// -- Linhas a semear (com dados ricos do Faker) -------------------

			$rows = [
				[
					'title'       => 'Manutenção de Sistemas — Plano Mensal',
					'subject'     => 'Suporte e manutenção preventiva/corretiva',
					'type_name'   => 'Prestação de Serviço',
					'frequency'   => 'monthly',
					'status'      => 'active',
					'renewable'   => true,
				],
				[
					'title'       => 'Célula Dedicada de Desenvolvimento',
					'subject'     => 'Time alocado (outsourcing) para evoluções contínuas',
					'type_name'   => 'Outsourcing',
					'frequency'   => 'monthly',
					'status'      => 'active',
					'renewable'   => true,
				],
				[
					'title'       => 'Contrato de Prestação PJ',
					'subject'     => 'Profissional PJ alocado 40h/semana',
					'type_name'   => 'Funcionário PJ (Pessoa Jurídica)',
					'frequency'   => 'monthly',
					'status'      => 'pending',
					'renewable'   => true,
				],
				[
					'title'       => 'Acordo de Sócios',
					'subject'     => 'Aporte e participação societária',
					'type_name'   => 'Sócio',
					'frequency'   => 'annual',
					'status'      => 'active',
					'renewable'   => false,
				],
				[
					'title'       => 'Venda de Insumos TI',
					'subject'     => 'Fornecimento de periféricos e insumos',
					'type_name'   => 'Venda de Insumos',
					'frequency'   => 'once',
					'status'      => 'completed',
					'renewable'   => false,
				],
			];

			$created = 0;
			$updated = 0;

			foreach ($rows as $r) {
				$typeName = $r['type_name'] ?? null;
				$typeId   = $typeName && isset($typeIdByName[$typeName]) ? $typeIdByName[$typeName] : null;
				$type     = $typeId ? ContractType::query()->find($typeId) : null;

				// Datas coerentes e variadas: início entre -2 e +1 mês
				$start = $now->addMonthsNoOverflow(random_int(-2, 1));
				$end   = $pickEndDateForType(
					$start,
					$type,
					fallbackMinM: ($r['frequency'] ?? 'monthly') === 'once' ? 0 : 3,
					fallbackMaxM: ($r['frequency'] ?? 'monthly') === 'annual' ? 12 : 24,
					tz: $tz
				);

				// Valor coerente com o tipo (respeita min/max do ContractType, se houver)
				$value = $pickValueForType($type, fallbackMin: 1_000.00, fallbackMax: 250_000.00);

				// Dados “reais” derivados do Faker
				$clientName  = $faker->company;
				$obligeeName = $clientName;         // tomador
				$obligorName = $faker->company;     // prestador/fornecedor
				$clientContact = $faker->phoneNumber . ' / ' . $faker->companyEmail;
				$vendorContact = $faker->phoneNumber . ' / ' . $faker->companyEmail;

				$payload = [
					'type'                  => $typeId,
					'title'                 => $r['title'],
					'subject'               => $r['subject'],
					'value'                 => number_format($value, 2, '.', ''), // string numérica (mutator aceita)
					'currency'              => 'BRL',
					'description'           => $faker->paragraphs(random_int(2, 4), true),
					'notes'                 => $faker->sentence(),
					PJC::COL_S_DT           => $start->format('Y-m-d'),
					PJC::COL_E_DT           => $end->format('Y-m-d'),
					PJC::COL_CDESC          => $type?->{BC::COL_TC} ?: $faker->paragraphs(3, true),
					'status'                => $r['status'] ?? $statusOptions[array_rand($statusOptions)],
					'renewable'             => (bool) ($r['renewable'] ?? false),
					PJC::COL_ARNW           => (bool) ($r['renewable'] ?? false),
					'frequency'             => $r['frequency'] ?? $freqOptions[array_rand($freqOptions)],

					// Evitamos FKs sem garantir existência (company/client_id/project_id/approved_by);
					// preenchemos campos textuais ricos no lugar:
					PJC::COL_CLIENT_NAME    => $clientName,
					PJC::COL_OBG_NAME       => $obligeeName,
					PJC::COL_OBL_NAME       => $obligorName,
					PJC::COL_OBG_IDF        => $randIdf(),
					PJC::COL_OBL_IDF        => $randIdf(),
					PJC::COL_OBG_ADDR       => $fullAddress($faker),
					PJC::COL_OBL_ADDR       => $fullAddress($faker),
					PJC::COL_OBG_CTC        => $clientContact,
					PJC::COL_OBL_CTC        => $vendorContact,

					// Testemunhas (nomes/documentos) e datas de assinatura variadas
					PJC::COL_WT_NM          => $faker->name,
					PJC::COL_WT2_NM         => $faker->name,
					PJC::COL_WT_IDF         => $randIdf(),
					PJC::COL_WT2_IDF        => $randIdf(),
					PJC::COL_WT_SIGN_AT     => $start->format('Y-m-d'),
					PJC::COL_WT2_SIGN_AT    => $start->addDays(1)->format('Y-m-d'),

					// Assinaturas do cliente/empresa: datas quando “active/completed”
					PJC::COL_CL_SIGN_AT     => in_array(($r['status'] ?? ''), ['active', 'completed'], true) ? $start->format('Y-m-d') : null,
					PJC::COL_CO_SIGN_AT     => in_array(($r['status'] ?? ''), ['active', 'completed'], true) ? $start->addDays(1)->format('Y-m-d') : null,

					// Arquivo/Anexos propositalmente omitidos (requerem FKs e storage)
					DC::COL_TABLE_CREATOR       => $systemUserId,
				];

				/** @var Contract $model */
				$model = Contract::query()->updateOrCreate(
					['title' => $payload['title']],
					$payload
				);

				// Número do contrato (não fillable)
				if (empty($model->{PJC::COL_CN})) {
					$model->{PJC::COL_CN} = $makeUniqueCN();
					$model->save();
				}

				$model->wasRecentlyCreated ? $created++ : $updated++;
			}

			Log::info("ContractsSeeder: created={$created}, updated={$updated}");
		}, 3);
	}
}
