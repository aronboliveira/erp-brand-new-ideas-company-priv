<?php

namespace App\Exports;

use App\Models\Revenue;
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;

final class AccountStatementExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;
    use DelegatesPythonExport;

    private const FREEZE_CELL  = 'A2';
    private const HEADER_RANGE = 'A1:D1';
    private const HEADINGS     = ['Statement Id', 'Date', 'Amount', 'Description'];
    private const PYTHON_EXPORTER    = 'AccountStatementExport';
    private const REMOVED_ATTRIBUTES = [
        'account_id',
        'add_receipt',
        'category_id',
        'created_at',
        'created_by',
        'customer_id',
        'payment_method',
        'reference',
        'updated_at'
    ];

    public function collection(): Collection
    {
        $data ??= collect();
        $user ??= null;
        $userOrRedirect ??= null;
        try {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse) {
                Log::warning(__METHOD__ . ' auth redirect', [
                    'class' => static::class
                ]);
                return collect();
            }
            $user = $userOrRedirect;
            if (empty($user)) {
                Log::error(__METHOD__ . ' null user', ['class' => static::class]);
                return collect();
            }
            Log::info(__METHOD__ . ' started', [
                'user_id' => $user->id ?? null,
                'class' => static::class
            ]);
            $data = Revenue::where('created_by', $user->id)->get();
            Log::info(__METHOD__ . ' fetched', [
                'count' => $data->count(),
                'class' => static::class
            ]);
            $data->each(function ($statement) {
                foreach (self::REMOVED_ATTRIBUTES as $attr) {
                    unset($statement->{$attr});
                }
            });
            Log::info(__METHOD__ . ' sanitized', [
                'count' => $data->count(),
                'class' => static::class
            ]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => static::class
            ]);
            $data = collect();
        }
        return $data;
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                try {
                    Log::info(__METHOD__ . ' styling started', [
                        'class' => static::class
                    ]);
                    $sheet = $event->sheet->getDelegate();
                    if (empty($sheet)) {
                        Log::warning(__METHOD__ . ' null sheet', [
                            'class' => static::class
                        ]);
                        return;
                    }
                    $sheet->getStyle(self::HEADER_RANGE)->applyFromArray([
                        'borders' => [
                            'horizontal' => [
                                'borderStyle' => 'thin',
                                'color' => ['argb' => '11333333']
                            ],
                            'vertical' => [
                                'borderStyle' => 'thin',
                                'color' => ['argb' => 'FF333333']
                            ]
                        ],
                        'fill' => [
                            'fillType' => 'solid',
                            'startColor' => ['argb' => '001122']
                        ],
                        'font' => ['bold' => true]
                    ]);
                    $sheet->freezePane(self::FREEZE_CELL);
                    Log::info(__METHOD__ . ' styling completed', [
                        'class' => static::class
                    ]);
                } catch (\Throwable $e) {
                    Log::error(__METHOD__ . ' styling exception', [
                        'error' => $e->getMessage(),
                        'class' => static::class
                    ]);
                }
            }
        ];
    }

    public function exportViaPython(?string $outputPath = null): string
    {
        $result ??= '';
        $user ??= null;
        $userOrRedirect ??= null;
        $data ??= [];
        try {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse) {
                Log::warning(__METHOD__ . ' auth redirect', [
                    'class' => static::class
                ]);
                return '';
            }
            $user = $userOrRedirect;
            if (empty($user)) {
                Log::error(__METHOD__ . ' null user', ['class' => static::class]);
                return '';
            }
            $statements = Revenue::where('created_by', $user->id)->get();
            $data = [
                'statements' => $statements->map(fn($s) => [
                    'id' => $s->id ?? 0,
                    'date' => ($s->date ?? now())->format('Y-m-d'),
                    'amount' => $s->amount ?? 0,
                    'description' => $s->description ?? '',
                ])->toArray(),
                'currency_symbol' => self::_prepareCurrencySymbol($user),
                'headings' => self::HEADINGS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('account_statement');
            }
            $result = self::_executePythonExporter(
                self::PYTHON_EXPORTER,
                $data,
                $outputPath
            );
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => static::class
            ]);
            $result = '';
        }
        return $result;
    }
}
