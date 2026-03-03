<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC, ProjectsConstants as PJC};
use App\Enums\{ProposalStatus, UserType};
use App\Models\Proposal;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProposalSeeder extends Seeder
{
	private const SECONDS_LIMIT = 6 * 10 ** 2;
	public function run(): void
	{
		$clock = microtime(true);
		$customers = DB::table(DC::TABLE_CUSTOMERS)
			->select('id', 'name')
			->get();

		if ($customers->isEmpty()) {
			Log::warning(self::class . ' skipped because no customers were found');
			return;
		}

		$baseCount = $customers->count();

		$target = 0;
		if ($this->command instanceof Command && $this->command->hasOption('count')) {
			$opt = (int) $this->command->option('count');
			if ($opt > 0) $target = $opt;
		}

		if ($target <= 0)
			$target = 4 * $baseCount;

		$leadIds      = DB::table(DC::TABLE_LEADS)->pluck('id')->all();
		$invoiceIds   = DB::table(DC::TABLE_INVS)->pluck('id')->all();
		$taxIds       = DB::table(DC::TABLE_TAXES)->pluck('id')->all();
		$categoryIds  = DB::table(DC::TABLE_PROD_SERV_CATS)->pluck('id')->all();
		$unitIds      = DB::table(DC::TABLE_PROD_SERV_UNITS)->pluck('id')->all();
		$employeeIds  = DB::table(DC::TABLE_EMPLOYEES)->pluck('id')->all();
		$qualifiedRejectors = DB::table(DC::TABLE_USERS)
			->whereIn('type', [UserType::Admin->value, UserType::SuperAdmin->value, UserType::Company->value, UserType::Accountant->value])
			->pluck('id')
			->all();

		$statusCases = ProposalStatus::cases();
		$now = Carbon::now();

		for ($i = 0; $i < $target; $i++) {

			if ((microtime(true) - $clock) > (!empty(self::SECONDS_LIMIT) ? self::SECONDS_LIMIT : 6 * 10 ** 2)) {
				Log::warning(self::class . ' seeding time limit reached, stopping early');
				return;
			}
			try {
				$customerRow = $customers[$i % $baseCount];

				$issueDate = $now->copy()->subDays(random_int(0, 120));
				$sendDate  = $issueDate->copy()->addDays(random_int(0, 3));
				$dueDate   = $issueDate->copy()->addDays(random_int(7, 60));
				$validTo   = $issueDate->copy()->addDays(random_int(10, 90));

				$amount      = random_int(50_000, 750_000) / 100;
				$discountMax = (int) round($amount * 0.25 * 100);
				$discount    = $discountMax > 0 ? random_int(0, $discountMax) / 100 : 0.0;
				$serviceFee  = random_int(0, (int) round($amount * 0.05 * 100)) / 100;
				$taxFee      = random_int(0, (int) round($amount * 0.25 * 100)) / 100;

				$statusEnum = $statusCases[array_rand($statusCases)];

				$isConvert = $statusEnum === ProposalStatus::Accepted
					&& !empty($invoiceIds)
					&& (bool) random_int(0, 1);

				$convertedInvoiceId = $isConvert
					? $invoiceIds[array_rand($invoiceIds)]
					: null;

				$leadId     = $leadIds ? $leadIds[array_rand($leadIds)] : null;
				$categoryId = $categoryIds ? $categoryIds[array_rand($categoryIds)] : null;
				$unitId     = $unitIds ? $unitIds[array_rand($unitIds)] : null;
				$taxId      = $taxIds && random_int(0, 1)
					? $taxIds[array_rand($taxIds)]
					: null;

				$employeesPointers = [];
				if (!empty($employeeIds) && random_int(0, 1)) {
					$employeesPointers = collect($employeeIds)
						->shuffle()
						->take(random_int(1, min(4, count($employeeIds))))
						->values()
						->all();
				}

				$signers = [];
				if (random_int(0, 1)) $signers[] = fake()->name();
				if (random_int(0, 1)) $signers[] = fake()->name();

				$rejectedAt = null;
				$rejectionReason = null;
				if ($statusEnum === ProposalStatus::Declined) {
					$rejectedAt = $validTo->copy()->subDays(random_int(0, 5));
					$rejectionReason = fake()->sentence(12);
				}

				$isSigned = in_array($statusEnum, [ProposalStatus::Accepted, ProposalStatus::Close], true);
				$signedAt = $isSigned
					? $issueDate->copy()->addDays(random_int(1, 45))
					: null;
				$signedByName = $isSigned ? fake()->name() : null;
				$viewedAt = random_int(0, 1)
					? $issueDate->copy()->addDays(random_int(0, 5))
					: null;

				$proposal = new Proposal();
				$title = fake()->sentence(6);
				do $proposalId = Str::uuid()->toString();
				while (Proposal::where(BC::COL_PPS_ID, $proposalId)->exists());
				$proposal->fill([
					'title'                 => $title,
					BC::COL_PPS_ID          => $proposalId,
					BC::COL_CST_ID          => $customerRow->id,
					'amount'                => $amount,
					'discount'              => $discount,
					BC::COL_SVC_FEE         => $serviceFee,
					BC::COL_TXS_FEE         => $taxFee,
					'reference'             => strtoupper(Str::random(10)),
					'description'           => fake()->paragraph(),
					'notes'                 => fake()->boolean(40) ? fake()->sentence(12) : null,
					'attachments'           => [],
					BC::COL_TC              => [],
					BC::COL_AUTORCC         => fake()->boolean(30),
					BC::COL_RCC_RL          => [],
					BC::COL_SD_DT           => $sendDate->toDateString(),
					BC::COL_ISS_DT          => $issueDate->toDateString(),
					PJC::COL_D_DATE         => $dueDate->toDateString(),
					BC::COL_DSC_APL         => $discount > 0.0 ? 1 : 0,
					BC::COL_CAT_ID          => $categoryId,
					'taxes'                 => $taxId ? [['id' => $taxId]] : [],
					BC::COL_VLD_TO          => $validTo,
					BC::COL_RQ_SIGN         => fake()->boolean(60),
					BC::COL_IS_SIGN         => $isSigned ? 1 : 0,
					BC::COL_SIGN_AT         => $signedAt,
					BC::COL_SIGN_BY         => null,
					BC::COL_SIGN_BY_NAME    => $signedByName,
					BC::COL_VW_AT           => $viewedAt,
					'payments'              => [],
					BC::COL_STT_LB          => $statusEnum->value,
					BC::COL_IS_CNV          => $isConvert ? 1 : 0,
					'version'               => random_int(1, 5),
					PJC::COL_REJ_BY         => $rejectedAt ? $qualifiedRejectors[array_rand($qualifiedRejectors)] : null,
					BC::COL_REJ_AT          => $rejectedAt,
					BC::COL_REJ_RS          => $rejectionReason,
					PJC::COL_LD_ID          => $leadId,
					BC::COL_CNV_INV_ID      => $convertedInvoiceId,
					BC::COL_TAX_ID          => $taxId,
					'employees'             => $employeesPointers,
					'customers'             => [$customerRow->id],
					'signers'               => $signers,
					'contract'              => null,
					'loan'                  => null,
					BC::COL_PRD_SV_UNT      => $unitId,
				]);
				(new \Symfony\Component\Console\Output\ConsoleOutput)->writeln("Criando relatório {$title} acerca da Proposta {$proposalId} para {$customerRow->id}");
				try {
					$proposal->save();
				} catch (\Throwable $e) {
					Log::warning(self::class . ' failed to seed proposal', [
						'error' => $e->getMessage(),
					]);
				}
			} catch (\Exception $e) {
				Log::warning(get_class($this) . ' failed: ' . $e->getMessage());
				continue;
			}
		}
	}
}
