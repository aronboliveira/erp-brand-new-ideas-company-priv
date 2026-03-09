<?php

namespace Database\Seeders;

use App\Config\Constants\{BillsConstants as BC, DatabaseConstants as DC};
use App\Enums\{EvaluationStatus, TransportationMethod};
use App\Models\WarehouseTransfer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\{ConsoleOutput, OutputInterface};

class WarehouseTransferSeeder extends Seeder
{
    private OutputInterface $out;

    public function run(): void
    {
        $this->out = new ConsoleOutput();

        $warehouses = collect(DB::select('select id from ' . DC::TABLE_WHS))
            ->map(fn($r) => is_object($r) ? (string) ($r->id ?? '') : '')
            ->filter(fn($id) => $id !== '')
            ->values();

        $products = collect(DB::select('select id from ' . DC::TABLE_PROD_SERVS))
            ->map(fn($r) => is_object($r) ? (string) ($r->id ?? '') : '')
            ->filter(fn($id) => $id !== '')
            ->values();

        $wCount = $warehouses->count();
        if ($wCount < 2) {
            $this->out->writeln('WarehouseTransferSeeder: skipped (need >= 2 warehouses).');
            return;
        }

        if ($products->isEmpty()) {
            $this->out->writeln('WarehouseTransferSeeder: skipped (no product/service rows found).');
            return;
        }

        $sourceCount = (int) ceil($wCount * 0.2);
        if ($sourceCount < 1) $sourceCount = 1;

        $sources = $warehouses->shuffle()->take($sourceCount)->values();

        $this->out->writeln(sprintf(
            'WarehouseTransferSeeder: creating transfers from %d source warehouses (of %d total warehouses) and %d products.',
            $sources->count(),
            $wCount,
            $products->count()
        ));

        $HARD_CAP = 2;
        $created = 0;

        foreach ($sources as $fromId) {
            if ($created >= $HARD_CAP) break;
            $iterations = random_int(1, 16);

            for ($i = 0; $i < $iterations; $i++) {
                $toId = null;
                $pickAttempts = 0;

                do {
                    $pickAttempts++;
                    $toId = (string) $warehouses->random();
                } while ($toId === $fromId && $pickAttempts < 64);

                if ($toId === $fromId) {
                    $this->out->writeln('WarehouseTransferSeeder: skipping iteration, could not pick different to/from warehouse.');
                    continue;
                }

                $productId = (string) $products->random();

                $codeAttempts = 0;
                $code = null;

                do {
                    $codeAttempts++;
                    $code = 'WRH-TRF-' . strtoupper((string) Str::uuid()) . '-' . (string) now()->timestamp;
                    $exists = DB::table(DC::TABLE_WRH_TRF)->where('code', $code)->exists();
                } while ($exists && $codeAttempts < 50);

                if (!$code || $exists) {
                    $this->out->writeln('WarehouseTransferSeeder: skipping iteration, could not generate unique code.');
                    continue;
                }

                $shipAt = now()->subDays(random_int(0, 30))->setTime(random_int(0, 23), random_int(0, 59), random_int(0, 59));
                $status = collect(EvaluationStatus::cases())->random()->value;
                $trp = collect(TransportationMethod::cases())->random()->value;

                $hasWrt = (bool) random_int(0, 1);
                $hasIns = (bool) random_int(0, 1);
                $hasExt = (bool) random_int(0, 1);

                $qty = random_int(1, 248);

                $data = [
                    'id' => (string) Str::uuid(),
                    'code' => $code,
                    BC::COL_PRD_ID => $productId,
                    BC::COL_FROM_WRH => $fromId,
                    BC::COL_TO_WRH => $toId,
                    'quantity' => $qty,
                    BC::COL_TRP_MTD => $trp,
                    BC::COL_TRP_CST => (string) number_format(random_int(0, 50000) / 100, 2, '.', ''),
                    BC::COL_SVC_FEE => (string) number_format(random_int(0, 20000) / 100, 2, '.', ''),
                    'status' => $status,
                    BC::COL_SCHD_DT => $shipAt->copy()->subHours(random_int(0, 48)),
                    BC::COL_SHIP_DT => $shipAt,
                    BC::COL_RCV_DT => $status === EvaluationStatus::Completed->value ? $shipAt->copy()->addHours(random_int(1, 96)) : null,
                    'date' => $shipAt->toDateString(),
                    'notes' => random_int(0, 1) ? 'seeded transfer' : null,
                    'attachments' => null,
                    'steps' => null,

                    BC::COL_HAS_WRT => $hasWrt,
                    BC::COL_WRT_CST => $hasWrt ? (string) number_format(random_int(0, 30000) / 100, 2, '.', '') : null,
                    BC::COL_WRT_DYS => $hasWrt ? random_int(30, 365) : null,
                    BC::COL_WRT_PLC => $hasWrt ? 'warranty policy (seed)' : null,

                    BC::COL_HAS_INS => $hasIns,
                    BC::COL_INS_CST => $hasIns ? (string) number_format(random_int(0, 50000) / 100, 2, '.', '') : null,
                    BC::COL_INS_PLC => $hasIns ? 'insurance policy (seed)' : null,

                    BC::COL_HAS_EXT_SEC => $hasExt,
                    BC::COL_EXT_SEC_CST => $hasExt ? (string) number_format(random_int(0, 20000) / 100, 2, '.', '') : null,
                ];

                // $this->out->writeln(sprintf(
                //     'WarehouseTransferSeeder: creating %s from=%s to=%s prd=%s qty=%d status=%s',
                //     $code,
                //     $fromId,
                //     $toId,
                //     $productId,
                //     $qty,
                //     $status
                // ));

                try {
                    WarehouseTransfer::query()->create($data);
                    $created++;
                } catch (\Throwable $e) {
                    $this->out->writeln('WarehouseTransferSeeder: failed create: ' . $e->getMessage());
                }
            }
        }

        $this->out->writeln('WarehouseTransferSeeder: created=' . $created);
    }
}
