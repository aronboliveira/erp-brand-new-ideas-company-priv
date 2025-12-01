<?php

namespace Database\Seeders;

use App\Config\Constants\{
	BanksConstants as BKC,
	BillsConstants as BC,
	UsersConstants as UC
};
use App\Enums\{
	PaymentMethod,
	PaymentStatus,
	TransferType
};
use App\Models\{
	BankAccount,
	ChartOfAccount,
	Payment,
	ProductServiceCategory,
	Vendor
};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InvoiceSeeder extends Seeder
{
	private int $records;
	private float $optionalRate;
	private \Faker\Generator $faker;

	public function __construct()
	{
		$this->records      = (int) env('SEED_PAYMENTS', 120);
		$this->optionalRate = max(0.0, min(1.0, (float) env('SEED_OPTIONALS_RATE', 0.65)));
		$this->faker        = \Faker\Factory::create('pt_BR');
	}

	public function run(): void
	{
		$bankIds   = BankAccount::query()->pluck('id')->all();
		$coaIds    = ChartOfAccount::query()->pluck('id')->all();
		$vdIds     = Vendor::query()->pluck('id')->all();
		$catIds    = ProductServiceCategory::query()->pluck('id')->all();

		$labelsPt  = PaymentStatus::labels('pt-br');

		$chunk = 40;
		for ($done = 0; $done < $this->records; $done += $chunk) {
			DB::transaction(function () use ($chunk, $bankIds, $coaIds, $vdIds, $catIds, $labelsPt) {
				for ($i = 0; $i < $chunk; $i++) {
					// Datas base
					$date   = $this->faker->dateTimeBetween('-180 days', 'now');
					$sched  = $this->maybe(0.35) ? $this->faker->dateTimeBetween($date, '+20 days') : null;

					// Método e status
					$method = $this->randomPaymentMethod();
					$pstat  = $this->randomPaymentStatus();       // para BC::COL_PAY_STT
					$status = $this->alignRowStatus($pstat);       // para coluna 'status' (HasPaymentColumns)

					// Valores
					$principal = $this->money($this->faker->randomFloat(2, 50, 25000));
					$interest  = $this->maybe(0.40) ? $this->money($principal * $this->randPct(0, 6)) : 0.00;
					$svcFee    = $this->maybe(0.30) ? $this->money($principal * $this->randPct(0, 2)) : 0.00;
					$taxFee    = $this->maybe(0.45) ? $this->money($principal * $this->randPct(0, 9)) : 0.00;
					$discount  = $this->maybe(0.50) ? $this->money(min($principal * $this->randPct(0, 20), $principal)) : 0.00;

					// Relacionamentos opcionais
					$accFrom  = $this->maybe(0.55) && $bankIds ? $this->faker->randomElement($bankIds) : null;
					$accTo    = $this->maybe(0.55) && $bankIds ? $this->faker->randomElement($bankIds) : null;
					$coa      = $this->maybe(0.60) && $coaIds   ? $this->faker->randomElement($coaIds)   : null;
					$vendorId = $this->maybe(0.70) && $vdIds    ? $this->faker->randomElement($vdIds)    : null;
					$catId    = $this->maybe(0.50) && $catIds   ? $this->faker->randomElement($catIds)   : null;

					// Transfer type / purpose
					$trfType  = $this->randomTransferType();
					$purpose  = $this->maybe(0.60) ? (string) $this->faker->numberBetween(100, 399) : '300';

					// Metadados de recebimento
					$receiptMeta = $this->maybe(0.30) ? [
						'nsu'         => strtoupper($this->faker->bothify('NSU########')),
						'auth_code'   => strtoupper($this->faker->bothify('AU####')),
						'gateway'     => $this->faker->randomElement(['CIELO', 'REDE', 'PAGARME', 'MERCADOPAGO']),
					] : null;

					// Campos de billing (grupo opcional)
					$bill = $this->maybe() ? $this->fakeBilling() : [];

					// Campos variáveis de segurança e reconciliação
					$canChargeback = $this->maybe(0.15); // chargeback raramente permitido
					$isSecured     = $this->maybe(0.25);

					// Timestamps por status
					$executedAt = null;
					$completedAt = null;
					$cancelledAt = null;
					if ($status === PaymentStatus::Completed) {
						$executedAt  = $date;
						$completedAt = $this->faker->dateTimeBetween($date, '+3 days');
					} elseif (in_array($status, [PaymentStatus::Cancelled, PaymentStatus::Failed, PaymentStatus::Declined, PaymentStatus::Expired], true)) {
						$cancelledAt = $this->faker->dateTimeBetween($date, '+10 days');
					}

					// Montagem do payload
					$data = array_filter([
						'date'                     => $date->format('Y-m-d'),
						'discount'                 => $discount,
						'recurring'                => $this->maybe(0.15) ? 'monthly' : null,

						// HasPaymentColumns
						'status'                   => $status->value,
						BC::COL_PAY_STT           => $pstat->value,
						BC::COL_STT_LB            => $labelsPt[$status->value] ?? ucfirst($status->value),
						BC::COL_PAY_MTD           => $method->value,

						// Relacionamentos/contas
						BC::COL_BACC_ID           => $coa ? null : ($accFrom ?? $accTo), // preferir conta quando não há CoA
						BC::COL_ACC_FROM          => $accFrom,
						BC::COL_ACC_TO            => $accTo,
						BKC::COL_COA              => $coa,
						UC::COL_VD_ID             => $vendorId,
						BC::COL_CAT_ID            => $catId,

						// Valores
						BC::COL_PRC_AMT           => $principal,
						BC::COL_INTR_AMT          => $interest,
						BC::COL_SVC_FEE           => $svcFee,
						BC::COL_TXS_FEE           => $taxFee,

						// Regras/flags
						BC::COL_TRF_TP            => $trfType->value,
						BC::COL_PPS_CD            => $purpose,
						BC::COL_IS_SCD            => $isSecured,
						BC::COL_CAN_CHG_BK        => $canChargeback,

						// Timestamps
						BC::COL_SCHD_TRF_TS       => $sched?->format('Y-m-d H:i:s'),
						BC::COL_EXC_AT            => $executedAt?->format('Y-m-d H:i:s'),
						BC::COL_CMP_AT            => $completedAt?->format('Y-m-d H:i:s'),
						BC::COL_CNC_AT            => $cancelledAt?->format('Y-m-d H:i:s'),
						BC::COL_CNC_RS            => $cancelledAt ? $this->faker->randomElement([
							'Solicitado pelo cliente',
							'Falha de saldo',
							'Timeout do provedor',
							'Dados inválidos'
						]) : null,

						// Recebimento
						BC::COL_RCP_MD            => $receiptMeta,

						// Outras descrições
						BC::COL_PPS_DS            => $this->maybe(0.40) ? $this->faker->sentence(6) : null,
						BC::COL_TXS_LST           => $this->maybe(0.35) ? $this->fakeTaxesList($principal, $discount) : null,
					], static fn($v) => $v !== null);

					// Billing (grupo) — merge
					$data = array_merge($data, $bill);

					// Ajustes por método de pagamento
					$this->applyMethodSpecificEnrichment($data, $method);

					try {
						Payment::create($data);
					} catch (\Throwable $e) {
						Log::error(static::class . ': falha ao criar Payment', [
							'error' => $e->getMessage(),
							'data'  => $this->redactSensitive($data),
						]);
					}
				}
			});
		}
	}

	/** Mantém mesma ideia de “densidade” de opcionais */
	private function maybe(?float $p = null): bool
	{
		$p = $p ?? $this->optionalRate;
		return $this->faker->boolean((int) round($p * 100));
	}

	private function money(float $v): float
	{
		return round(max($v, 0.0), 2);
	}

	private function randPct(float $min = 0, float $max = 20): float
	{
		return $this->faker->randomFloat(4, $min / 100, $max / 100);
	}

	private function randomPaymentMethod(): PaymentMethod
	{
		// distribuição levemente inclinada para Pix/cartão/transferência
		$pool = [
			PaymentMethod::Pix,
			PaymentMethod::Pix,
			PaymentMethod::Pix,
			PaymentMethod::CardDebit,
			PaymentMethod::CardCredit,
			PaymentMethod::BankTransfer,
			PaymentMethod::Ted,
			PaymentMethod::Doc,
			PaymentMethod::WireTransfer,
			PaymentMethod::Cash,
			PaymentMethod::Other,
		];
		return $pool[array_rand($pool)];
	}

	private function randomPaymentStatus(): PaymentStatus
	{
		// distribuição prática para rotinas reais
		$pool = [
			PaymentStatus::Completed,
			PaymentStatus::Completed,
			PaymentStatus::Completed,
			PaymentStatus::Processing,
			PaymentStatus::Processing,
			PaymentStatus::Pending,
			PaymentStatus::Pending,
			PaymentStatus::Cancelled,
			PaymentStatus::Failed,
			PaymentStatus::Refunded,
			PaymentStatus::Declined,
			PaymentStatus::Disputed,
			PaymentStatus::Expired,
		];
		return $pool[array_rand($pool)];
	}

	private function alignRowStatus(PaymentStatus $p): PaymentStatus
	{
		// Em geral, mantemos o mesmo; em casos “intermediários”, normalizamos
		return match ($p) {
			PaymentStatus::Undefined => PaymentStatus::Pending,
			default => $p,
		};
	}

	private function randomTransferType(): TransferType
	{
		$pool = [
			TransferType::Service,
			TransferType::Purchase,
			TransferType::Internal,
			TransferType::Refund,
			TransferType::TaxPayment,
			TransferType::LoanPayment,
			TransferType::Salary,
			TransferType::Other,
		];
		return $pool[array_rand($pool)];
	}

	private function applyMethodSpecificEnrichment(array &$data, PaymentMethod $method): void
	{
		// Cartão: digitos finais, bandeira, etc.
		if ($method->isCard()) {
			$data[BC::COL_CD_DG]  = $this->faker->numerify('####');
			$data[BC::COL_CD_FLG] = $this->faker->randomElement(['VISA', 'MASTERCARD', 'ELO', 'AMEX']);
			$data[BC::COL_CD_EX_M] = (string) $this->faker->numberBetween(1, 12);
			$data[BC::COL_CD_EX_Y] = (string) $this->faker->numberBetween((int) date('Y'), (int) date('Y') + 6);
			$data[BC::COL_CD_HNM] = Str::upper($this->faker->name());
		}

		// Pix: chave e QR
		if ($method === PaymentMethod::Pix) {
			$data[BC::COL_PIX_KEY] = $this->faker->randomElement([
				$this->faker->email(),
				$this->faker->cpf(false),
				$this->faker->cellphoneNumber()
			]);
			if ($this->maybe(0.40)) {
				$data[BC::COL_PIX_QR] = '00020126...'; // string simbólica
			}
		}
	}

	private function fakeTaxesList(float $principal, float $discount): array
	{
		$net = max($principal - $discount, 0.0);
		$n   = $this->faker->numberBetween(1, 3);
		$out = [];
		for ($i = 0; $i < $n; $i++) {
			$rate = $this->faker->randomFloat(2, 1, 8);
			$out[] = [
				'name'   => $this->faker->randomElement(['ISS', 'PIS', 'COFINS', 'IOF']),
				'rate'   => $rate,
				'amount' => $this->money($net * ($rate / 100)),
			];
		}
		return $out;
	}

	private function fakeBilling(): array
	{
		return [
			BC::COL_BL_NAME => $this->faker->company(),
			BC::COL_BL_EMAIL => Str::slug($this->faker->company()) . '@example.com',
			BC::COL_BL_ADR  => $this->faker->streetAddress(),
			BC::COL_BL_TEL  => $this->faker->phoneNumber(),
			BC::COL_BL_ZIP  => preg_replace('/\D+/', '', $this->faker->postcode()),
			BC::COL_BL_CTY  => $this->faker->city(),
			BC::COL_BL_ST   => $this->faker->stateAbbr(),
			BC::COL_BL_CTR  => 'BR',
			BC::COL_BL_DTL  => $this->maybe(0.40) ? 'CNPJ ' . $this->faker->numerify('##.###.###/####-##') : null,
		];
	}

	private function redactSensitive(array $data): array
	{
		foreach ([BC::COL_CD_DG, BC::COL_CD_HNM, BC::COL_PIX_KEY] as $s) {
			if (isset($data[$s])) {
				$data[$s] = '[redacted]';
			}
		}
		return $data;
	}
}
