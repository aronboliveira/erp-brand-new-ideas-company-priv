<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str as Str;

use App\Config\Constants\DatabaseConstants as DC;
use App\Config\Constants\PlansConstants as PLC;
use App\Models\Plan as Pln;

final class PlanSeeder extends Seeder
{
	public function run(): void
	{
		DB::transaction(function () {
			$creatorId = DC::DEFAULT_UUID;

			$plans = [
				[
					PLC::COL_NM  => 'Free',
					PLC::COL_DUR => 'month',
					PLC::COL_PC  => 0.00,
					PLC::COL_MAX_U  => 1,
					PLC::COL_MAX_CR => 50,
					PLC::COL_MAX_V  => 5,
					PLC::COL_MAX_CL => 10,
					PLC::COL_SL    => 5.00,   // GB
					PLC::COL_GPT   => 0,
					PLC::COL_CRM   => 1,
					PLC::COL_HRM   => 0,
					PLC::COL_ACC   => 0,
					PLC::COL_PJ    => 1,
					PLC::COL_POS   => 1,
					PLC::COL_DESC  => 'Plano gratuito para testes e uso individual.',
					PLC::COL_IMG   => null,
				],
				[
					PLC::COL_NM  => 'Starter',
					PLC::COL_DUR => 'month',
					PLC::COL_PC  => 49.90,
					PLC::COL_MAX_U  => 5,
					PLC::COL_MAX_CR => 1_000,
					PLC::COL_MAX_V  => 50,
					PLC::COL_MAX_CL => 200,
					PLC::COL_SL    => 50.00,
					PLC::COL_GPT   => 1,
					PLC::COL_CRM   => 1,
					PLC::COL_HRM   => 0,
					PLC::COL_ACC   => 0,
					PLC::COL_PJ    => 1,
					PLC::COL_POS   => 2,
					PLC::COL_DESC  => 'Essencial para pequenas equipes.',
					PLC::COL_IMG   => null,
				],
				[
					PLC::COL_NM  => 'Business',
					PLC::COL_DUR => 'month',
					PLC::COL_PC  => 149.90,
					PLC::COL_MAX_U  => 25,
					PLC::COL_MAX_CR => 10_000,
					PLC::COL_MAX_V  => 500,
					PLC::COL_MAX_CL => 2_000,
					PLC::COL_SL    => 200.00,
					PLC::COL_GPT   => 1,
					PLC::COL_CRM   => 1,
					PLC::COL_HRM   => 1,
					PLC::COL_ACC   => 1,
					PLC::COL_PJ    => 1,
					PLC::COL_POS   => 3,
					PLC::COL_DESC  => 'Recursos completos para PME.',
					PLC::COL_IMG   => null,
				],
				[
					PLC::COL_NM  => 'Enterprise',
					PLC::COL_DUR => 'month',
					PLC::COL_PC  => 499.90,
					PLC::COL_MAX_U  => 500,
					PLC::COL_MAX_CR => 500_000,
					PLC::COL_MAX_V  => 50_000,
					PLC::COL_MAX_CL => 200_000,
					PLC::COL_SL    => 2048.00,
					PLC::COL_GPT   => 1,
					PLC::COL_CRM   => 1,
					PLC::COL_HRM   => 1,
					PLC::COL_ACC   => 1,
					PLC::COL_PJ    => 1,
					PLC::COL_POS   => 4,
					PLC::COL_DESC  => 'Para operações com alta escala e SLA.',
					PLC::COL_IMG   => null,
				],
				[
					PLC::COL_NM  => 'Lifetime Pro',
					PLC::COL_DUR => 'lifetime',
					PLC::COL_PC  => 2999.00,
					PLC::COL_MAX_U  => 25,
					PLC::COL_MAX_CR => 20_000,
					PLC::COL_MAX_V  => 1_000,
					PLC::COL_MAX_CL => 4_000,
					PLC::COL_SL    => 512.00,
					PLC::COL_GPT   => 1,
					PLC::COL_CRM   => 1,
					PLC::COL_HRM   => 1,
					PLC::COL_ACC   => 0,
					PLC::COL_PJ    => 1,
					PLC::COL_POS   => 5,
					PLC::COL_DESC  => 'Licença vitalícia para equipes.',
					PLC::COL_IMG   => null,
				],
				[
					PLC::COL_NM  => 'Lifetime Enterprise',
					PLC::COL_DUR => 'lifetime',
					PLC::COL_PC  => 9999.00,
					PLC::COL_MAX_U  => 1_000,
					PLC::COL_MAX_CR => 2_000_000,
					PLC::COL_MAX_V  => 200_000,
					PLC::COL_MAX_CL => 800_000,
					PLC::COL_SL    => 10_000.00,
					PLC::COL_GPT   => 1,
					PLC::COL_CRM   => 1,
					PLC::COL_HRM   => 1,
					PLC::COL_ACC   => 1,
					PLC::COL_PJ    => 1,
					PLC::COL_POS   => 6,
					PLC::COL_DESC  => 'Vitalício para grandes corporações.',
					PLC::COL_IMG   => null,
				],
			];

			foreach ($plans as $p) {
				if (Pln::where(PLC::COL_NM, $p[PLC::COL_NM])->exists()) continue;

				do $planId = Str::uuid()->toString();
				while (Pln::where('id', $planId)->exists());

				do $planQueryKey = Str::uuid()->toString();
				while (Pln::where('query_key', $planQueryKey)->exists());

				$pl = new Pln();
				$pl->id                      = $planId;
				$pl->query_key               = $planQueryKey;
				$pl->{PLC::COL_NM}           = $p[PLC::COL_NM];
				$pl->{PLC::COL_DUR}          = $p[PLC::COL_DUR];
				$pl->{PLC::COL_PC}           = $p[PLC::COL_PC];
				$pl->{PLC::COL_MAX_U}        = $p[PLC::COL_MAX_U];
				$pl->{PLC::COL_MAX_CR}       = $p[PLC::COL_MAX_CR];
				$pl->{PLC::COL_MAX_V}        = $p[PLC::COL_MAX_V];
				$pl->{PLC::COL_MAX_CL}       = $p[PLC::COL_MAX_CL];
				$pl->{PLC::COL_SL}           = $p[PLC::COL_SL];
				$pl->{PLC::COL_GPT}          = $p[PLC::COL_GPT];
				$pl->{PLC::COL_CRM}          = $p[PLC::COL_CRM];
				$pl->{PLC::COL_HRM}          = $p[PLC::COL_HRM];
				$pl->{PLC::COL_ACC}          = $p[PLC::COL_ACC];
				$pl->{PLC::COL_PJ}           = $p[PLC::COL_PJ];
				$pl->{PLC::COL_POS}          = $p[PLC::COL_POS];
				$pl->{PLC::COL_DESC}         = $p[PLC::COL_DESC];
				$pl->{PLC::COL_IMG}          = $p[PLC::COL_IMG];
				$pl->{DC::TABLE_CREATOR}     = $creatorId;
				$pl->setAttribute(DC::TABLE_UPDATER, null);
				$pl->save();
			}
		}, 3);
	}
}
