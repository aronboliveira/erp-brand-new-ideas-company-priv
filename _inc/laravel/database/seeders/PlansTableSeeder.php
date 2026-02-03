<?php

namespace Database\Seeders;

use App\Config\Constants\{DatabaseConstants as DC, PlansConstants as PLC};
use App\Enums\Frequency;
use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Str;

class PlansTableSeeder extends Seeder
{
    /**
     * Used by other seeders (e.g. UsersTableSeeder) as the default plan reference.
     */
    public static ?string $planId = null;

    /**
     * Keep this constant stable forever, otherwise all deterministic UUIDs will change.
     * You can generate any fixed UUID and commit it once.
     */
    private const UUID_NAMESPACE = '1b67d2c0-2b35-4f1b-a1d0-8dff3a2c9d11';

    private const DURATIONS = [
        'lifetime',
        'month',
        'semimonthly',
        'quarterly',
        'semiannual',
        'year',
    ];

    /**
     * Price multipliers relative to MONTHLY price.
     * (Values are only for consistent test data; adjust if your business rules differ.)
     */
    private const DURATION_PRICE_MULTIPLIER = [
        'month' => 1.00,
        'semimonthly' => 0.50,
        'quarterly' => 2.85,    // 3 * 0.95
        'semiannual' => 5.40,   // 6 * 0.90
        'year' => 10.20,        // 12 * 0.85
        'lifetime' => 20.00,    // 24 * ~0.833
    ];

    private const TIERS = [
        'Free' => [
            'monthly_price' => 0.00,
            PLC::COL_MAX_U => 5,
            PLC::COL_MAX_CR => 5,
            PLC::COL_MAX_V => 5,
            PLC::COL_MAX_CL => 5,
            PLC::COL_SL => 1024.00,
            PLC::COL_GPT => 1,
            PLC::COL_CRM => 1,
            PLC::COL_HRM => 1,
            PLC::COL_ACC => 1,
            PLC::COL_PJ => 1,
            PLC::COL_POS => 1,
            PLC::COL_IMG => 'plans/free_plan.png',
            PLC::COL_DESC => 'Basic access for evaluation and small teams.',
        ],
        'Starter' => [
            'monthly_price' => 19.90,
            PLC::COL_MAX_U => 10,
            PLC::COL_MAX_CR => 200,
            PLC::COL_MAX_V => 50,
            PLC::COL_MAX_CL => 50,
            PLC::COL_SL => 5120.00,
            PLC::COL_GPT => 10,
            PLC::COL_CRM => 1,
            PLC::COL_HRM => 1,
            PLC::COL_ACC => 1,
            PLC::COL_PJ => 1,
            PLC::COL_POS => 0,
            PLC::COL_IMG => 'plans/starter_plan.png',
            PLC::COL_DESC => 'Starter plan for growing teams.',
        ],
        'Pro' => [
            'monthly_price' => 49.90,
            PLC::COL_MAX_U => 50,
            PLC::COL_MAX_CR => 2000,
            PLC::COL_MAX_V => 500,
            PLC::COL_MAX_CL => 500,
            PLC::COL_SL => 20480.00,
            PLC::COL_GPT => 50,
            PLC::COL_CRM => 1,
            PLC::COL_HRM => 1,
            PLC::COL_ACC => 1,
            PLC::COL_PJ => 1,
            PLC::COL_POS => 1,
            PLC::COL_IMG => 'plans/pro_plan.png',
            PLC::COL_DESC => 'Advanced features for mature operations.',
        ],
        'Enterprise' => [
            'monthly_price' => 149.90,
            PLC::COL_MAX_U => 200,
            PLC::COL_MAX_CR => 20000,
            PLC::COL_MAX_V => 5000,
            PLC::COL_MAX_CL => 5000,
            PLC::COL_SL => 102400.00,
            PLC::COL_GPT => 200,
            PLC::COL_CRM => 1,
            PLC::COL_HRM => 1,
            PLC::COL_ACC => 1,
            PLC::COL_PJ => 1,
            PLC::COL_POS => 1,
            PLC::COL_IMG => 'plans/enterprise_plan.png',
            PLC::COL_DESC => 'High-capacity plan for large organizations.',
        ],
    ];

    public function run(): void
    {
        self::$planId = DC::DEFAULT_PLAN;

        Plan::unguarded(function (): void {
            DB::transaction(function (): void {
                foreach (self::TIERS as $tierName => $tierCfg)
                    foreach (self::DURATIONS as $duration)
                        $this->upsertPlan($this->makePlanPayload($tierName, $duration, $tierCfg));
            });
        });
    }

    private function makePlanPayload(string $tierName, string $duration, array $tierCfg): array
    {
        // Keep one canonical "Free" plan to satisfy Plan::getPlan() fallback by name.
        $isDefaultFree = $tierName === 'Free' && $duration === 'lifetime';

        $id = $isDefaultFree
            ? DC::DEFAULT_PLAN
            : $this->uuid5("plan|{$tierName}|{$this->durationKey($duration)}");

        $monthly = (float)($tierCfg['monthly_price'] ?? 0.0);
        $mult = (float)(self::DURATION_PRICE_MULTIPLIER[$duration] ?? 1.0);
        $price = number_format($monthly * $mult, 2, '.', '');

        $name = $isDefaultFree
            ? 'Free'
            : "{$tierName} - " . Str::title(str_replace('_', ' ', $duration));

        return [
            'id' => $id,
            'query_key' => $id, // stable and unique; you may decouple later if you need.
            PLC::COL_NM => $name,
            PLC::COL_DUR => $duration,
            PLC::COL_PC => $price,
            PLC::COL_MAX_U => (int)($tierCfg[PLC::COL_MAX_U] ?? 0),
            PLC::COL_MAX_CR => (int)($tierCfg[PLC::COL_MAX_CR] ?? 0),
            PLC::COL_MAX_V => (int)($tierCfg[PLC::COL_MAX_V] ?? 0),
            PLC::COL_MAX_CL => (int)($tierCfg[PLC::COL_MAX_CL] ?? 0),
            PLC::COL_SL => number_format((float)($tierCfg[PLC::COL_SL] ?? 0.0), 2, '.', ''),
            PLC::COL_GPT => (int)($tierCfg[PLC::COL_GPT] ?? 0),
            PLC::COL_CRM => (int)($tierCfg[PLC::COL_CRM] ?? 0),
            PLC::COL_HRM => (int)($tierCfg[PLC::COL_HRM] ?? 0),
            PLC::COL_ACC => (int)($tierCfg[PLC::COL_ACC] ?? 0),
            PLC::COL_PJ => (int)($tierCfg[PLC::COL_PJ] ?? 0),
            PLC::COL_POS => (int)($tierCfg[PLC::COL_POS] ?? 0),
            PLC::COL_DESC => (string)($tierCfg[PLC::COL_DESC] ?? ''),
            PLC::COL_IMG => (string)($tierCfg[PLC::COL_IMG] ?? null),
        ];
    }

    private function upsertPlan(array $plan): void
    {
        $id = $plan['id'] ?? null;
        $qk = $plan['query_key'] ?? null;
        $name = $plan[PLC::COL_NM] ?? null;
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        if (!Str::isUuid((string)$id) || !Str::isUuid((string)$qk) || !$name) {
            Log::warning('PlansTableSeeder: invalid plan payload', [
                'id' => $id,
                'query_key' => $qk,
                'name' => $name,
            ]);
            $out->writeln('<error>PlansTableSeeder: invalid plan payload</error>');
            return;
        }

        $model = Plan::query()->whereKey($id)->first()
            ?? Plan::query()->where('query_key', $qk)->first()
            ?? Plan::query()->where(PLC::COL_NM, $name)->first();

        if ($model) {
            $model->forceFill($plan);
            $model->setAttribute($model->getKeyName(), $id);
            $model->save();
            $out->writeln("<info>PlansTableSeeder: updated plan '{$plan[PLC::COL_NM]}' with query_key '{$plan['query_key']}'</info>");
            Log::warning("PlansTableSeeder: updated plan '{$plan[PLC::COL_NM]}'");
            return;
        }

        Plan::create($plan);
        Log::warning("PlansTableSeeder: created plan '{$plan[PLC::COL_NM]}'");
        $out->writeln("<info>PlansTableSeeder: created plan '{$plan[PLC::COL_NM]}' with query_key '{$plan['query_key']}'</info>");
    }

    private function durationKey(string $duration): string
    {
        // Uses the enum only to normalize equivalent duration strings for stable concatenation.
        // (lifetime is not part of the enum, so it stays as-is.)
        $freq = Frequency::normalize($duration);
        return $freq?->value ?? $duration;
    }

    /**
     * Deterministic UUIDv5 (RFC 4122) from namespace + name.
     */
    private function uuid5(string $name, string $namespace = self::UUID_NAMESPACE): string
    {
        $ns = str_replace(['-', '{', '}'], '', $namespace);
        if (!ctype_xdigit($ns) || strlen($ns) !== 32)
            throw new \InvalidArgumentException("Invalid UUID namespace: {$namespace}");

        $nsBytes = hex2bin($ns);
        $hash = sha1($nsBytes . $name);

        $timeLow = substr($hash, 0, 8);
        $timeMid = substr($hash, 8, 4);
        $timeHi  = substr($hash, 12, 4);
        $clkSeq  = substr($hash, 16, 4);
        $node    = substr($hash, 20, 12);

        $timeHiInt = hexdec($timeHi);
        $timeHiInt = ($timeHiInt & 0x0fff) | 0x5000; // version 5
        $timeHi = str_pad(dechex($timeHiInt), 4, '0', STR_PAD_LEFT);

        $clkSeqInt = hexdec($clkSeq);
        $clkSeqInt = ($clkSeqInt & 0x3fff) | 0x8000; // variant RFC 4122
        $clkSeq = str_pad(dechex($clkSeqInt), 4, '0', STR_PAD_LEFT);

        return sprintf(
            '%s-%s-%s-%s-%s',
            $timeLow,
            $timeMid,
            $timeHi,
            $clkSeq,
            $node
        );
    }
}
