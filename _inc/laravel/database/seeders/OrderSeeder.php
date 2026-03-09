<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BillsConstants as BC,
	DatabaseConstants as DC,
	SettingsConstants as SC,
	UsersConstants as UC
};
use App\Enums\{
	MonthName,
	PaymentMethod,
	PaymentStatus
};
use App\Models\Order;
use App\Traits\EnsuresSystemUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class OrderSeeder extends Seeder
{
	use EnsuresSystemUser;

	/**
	 * Quantidade de pedidos a gerar
	 */
	private const TOTAL = 256;

	public function run(): void
	{
		DB::transaction(function () {
			$faker        = \Faker\Factory::create('pt_BR');
			$systemUserId = $this->ensureSystemUser();
			$tz           = 'America/Sao_Paulo';

			// Planejamento de dados-base (FKs e suporte)
			$planId = $this->resolvePlanId();
			$userIds = $this->safeUserPool(self::TOTAL); // distintos (respeita UNIQUE em user_id) ou vazio

			$created = 0;
			$failed  = 0;
			$HARD_CAP = 2; // original: self::TOTAL (256)

			for ($i = 0; $i < $HARD_CAP; $i++) {
				try {
					// Método de pagamento preferencial (60% Pix, 25% Crédito, 15% Débito)
					$method = $this->pickWeighted([
						PaymentMethod::Pix->value        => 60,
						PaymentMethod::CardCredit->value => 25,
						PaymentMethod::CardDebit->value  => 15,
					]);

					// Preço/desconto coerentes
					$price    = $this->money2($faker->randomFloat(2, 79, 8999));
					$discount = $this->money2($faker->randomFloat(2, 0, $price * 0.25));

					// Parcelas (apenas faz sentido para crédito; demais ficam 1)
					$installments = $method === PaymentMethod::CardCredit->value
						? random_int(1, 12)
						: 1;

					// Instrumentos (requisito: ao menos um entre cartão | pix | payslip)
					$card = $method === PaymentMethod::CardCredit->value || $method === PaymentMethod::CardDebit->value
						? $this->makeCardData($faker)
						: null;

					$pixKey = $method === PaymentMethod::Pix->value
						? $this->makePixKey($faker)
						: null;

					// Status inicial realista
					$status = $this->pick([
						PaymentStatus::Pending->value,
						PaymentStatus::Processing->value,
						PaymentStatus::Authorized->value,
						PaymentStatus::Completed->value,
					]);

					// FK de usuário (único). Se pool esgotar, usa NULL (permitido e NÃO conflita com UNIQUE)
					$userId = $userIds[$i] ?? null;
					$orderId = 'ORD-' . Str::upper(Str::random(12)); // UNIQUE externo
					$name = $faker->boolean(75) ? $faker->company() . ' Service' : ($faker->boolean(50) ? $faker->company() . ' Product' : $faker->sentence(3));
					// (new \Symfony\Component\Console\Output\ConsoleOutput
					// )->writeln("Criando Pedido {$orderId} de {$name} via {$method} para {$userId}");
					$payload = [
						UC::COL_USER_ID       => $userId,                    // UNIQUE e nullable
						BC::COL_OD_ID         => $orderId, // UNIQUE externo
						'name'                => $name,
						'email'               => $faker->unique()->safeEmail(),
						UC::COL_PLAN_ID       => $planId,                    // FK obrigatória
						UC::COL_PLAN_NM       => $this->pick(['Starter', 'Business', 'Enterprise']),
						'price'               => $price,
						'discount'            => $discount,
						BC::COL_PRC_CUR       => strtoupper(SC::DEF_SITE_CURRENCY_ID ?? 'BRL'),
						BC::COL_N_INTR        => $installments,

						// Cartão (preenchido apenas quando necessário)
						BC::COL_CD_FLG        => $card['flag']     ?? null,
						BC::COL_CD_NB         => $card['number']   ?? null,
						BC::COL_CD_DG         => $card['digits']   ?? null,
						BC::COL_CD_HNM        => $card['holder']   ?? null,
						BC::COL_CD_EX_M       => $card['exp_mon']  ?? null, // MonthName::value
						BC::COL_CD_EX_Y       => $card['exp_year'] ?? null, // string YYYY

						// Pix (preenchido apenas quando necessário)
						BC::COL_PIX_KEY       => $pixKey,

						// Tributos (opcional: deixa nulos/array vazio; modelo filtra IDs inexistentes)
						BC::COL_TAX_ID        => null,
						BC::COL_OT_TX_ID      => [],

						// Payslip: evitamos setar FK para não forçar dependências
						BC::COL_PSLP_ID       => null,

						// Status / método
						BC::COL_PAY_STT       => $status,
						BC::COL_PAY_TP        => $method,

						// Recibo e metadados auxiliares
						'receipt'             => null,
						BC::COL_RCP_MD        => [
							'ip'         => $faker->ipv4(),
							'user_agent' => $faker->userAgent(),
							'notes'      => $faker->optional(0.3)->sentence(),
						],

						// Auditoria
						DC::COL_TABLE_CREATOR     => $systemUserId,
					];

					try {
						Order::create($payload);
						$created++;
					} catch (\Throwable $e) {
						$failed++;
						Log::warning(self::class . ' failed to create Order row', [
							'i'       => $i,
							'error'   => $e->getMessage(),
							'payload' => [
								'user_id' => $payload[UC::COL_USER_ID],
								'plan_id' => $payload[UC::COL_PLAN_ID],
								'email'   => $payload['email'],
								'method'  => $payload[BC::COL_PAY_TP],
							],
						]);
					}
				} catch (\Exception $e) {
					Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
					continue;
				}
			}

			Log::info(self::class . " finished: created={$created}, failed={$failed}");
		});
	}

	private function money2(float $v): float
	{
		return (float) number_format($v, 2, '.', '');
	}

	/**
	 * Seleciona 1 chave segundo pesos (inteiros).
	 * @param array<string,int> $weighted
	 */
	private function pickWeighted(array $weighted): string
	{
		$sum = array_sum($weighted);
		$r = random_int(1, max($sum, 1));
		foreach ($weighted as $k => $w) {
			$r -= $w;
			if ($r <= 0) return $k;
		}
		return array_key_first($weighted);
	}

	/**
	 * Seleciona 1 item simples.
	 * @param array<int,string> $options
	 */
	private function pick(array $options): string
	{
		return $options[array_rand($options)];
	}

	/**
	 * Gera dados de cartão coerentes com as validações do modelo.
	 * - Número: 16 dígitos
	 * - Dígitos finais: últimos 4
	 * - Holder: nome
	 * - Expiração: mês/ano atualizados (nunca no passado)
	 *
	 * @return array{number:string,digits:string,holder:string,flag:string,exp_mon:string,exp_year:string}
	 */
	private function makeCardData(\Faker\Generator $faker): array
	{
		$number = (string) random_int(4_000_000_000_000_000, 4_999_999_999_999_999);
		$digits = substr($number, -4);
		$holder = strtoupper($faker->firstName() . ' ' . $faker->lastName());
		$flag   = $this->pick(['VISA', 'MASTERCARD', 'ELO', 'AMEX']);

		$nowYear = (int) now()->format('Y');
		$year    = (string) random_int($nowYear, $nowYear + 5);

		// Mês válido (se ano atual, mês >= atual)
		$currMon = (int) now()->format('n');
		$monIso  = random_int(1, 12);
		if ((int)$year === $nowYear && $monIso < $currMon) {
			$monIso = $currMon;
		}
		$expMon  = MonthName::normalize((string) $monIso)->value;

		return [
			'number'   => $number,
			'digits'   => $digits,
			'holder'   => $holder,
			'flag'     => $flag,
			'exp_mon'  => $expMon,
			'exp_year' => $year,
		];
	}

	/**
	 * Pix válido segundo regras do modelo:
	 * - email OU cpf/cnpj (11/14 dígitos) OU telefone (8–15) OU chave aleatória [A-Za-z0-9-] máx. 36.
	 */
	private function makePixKey(\Faker\Generator $faker): string
	{
		$choice = random_int(1, 4);
		return match ($choice) {
			1 => strtolower($faker->unique()->safeEmail()),
			2 => str_pad((string) random_int(0, 99999999999), 11, '0', STR_PAD_LEFT), // CPF
			3 => '+55' . str_pad((string) random_int(0, 99999999999), 11, '0', STR_PAD_LEFT), // telefone
			default => strtoupper(Str::uuid()->toString()), // chave aleatória (<=36)
		};
	}

	/**
	 * Obtém um Plan ID válido; se não houver, tenta usar DEFAULT_PLAN.
	 * Caso nenhuma opção seja viável, lança exceção para falhar cedo.
	 */
	private function resolvePlanId(): string
	{
		$planId = (string) (DB::table(DC::TABLE_PLANS)->value('id') ?? '');
		if ($planId !== '') return $planId;

		// fallback para DEFAULT_PLAN, se existir
		$fallback = DC::DEFAULT_PLAN ?? null;
		if ($fallback && DB::table(DC::TABLE_PLANS)->where('id', $fallback)->exists()) {
			return $fallback;
		}

		throw new \RuntimeException('OrderSeeder: nenhum plano encontrado. Crie ao menos um registro em "' . DC::TABLE_PLANS . '".');
	}

	/**
	 * Retorna até $limit IDs distintos de usuários para preencher user_id (único).
	 * Se não houver usuários suficientes, o restante ficará NULL (permitido).
	 *
	 * @return array<int,string>
	 */
	private function safeUserPool(int $limit): array
	{
		$ids = DB::table(DC::TABLE_USERS)->pluck('id')->slice(0, $limit)->values()->all();
		// Garante unicidade (já é único por construção) e reindexa
		return array_values(array_unique(array_map('strval', $ids)));
	}
}
