<?php

namespace App\Exports;

use App\Models\{Bill, ProductServiceCategory};
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

final class BillExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;
    use DelegatesPythonExport;

    private const BODY_FILL_OPACITY   = '33FFFFFF';
    private const BORDER_COLOR        = '808080';
    private const EXPENSE_TYPE        = 'expense';
    private const FREEZE_CELL         = 'A2';
    private const HEADER_BORDER_COLOR = '000000';
    private const HEADER_FILL_COLOR   = 'FF1F1F2F';
    private const HEADER_ROW          = '1:1';
    private const HEADINGS = [
        'Bill No',
        'Bill Date',
        'Due Date',
        'Order No',
        'Status',
        'Send Date',
        'Category'
    ];
    private const PYTHON_EXPORTER = 'BillExport';
    private const UNSET_FIELDS = [
        'created_at',
        'created_by',
        'customer_id',
        'discount_apply',
        'id',
        'shipping_display',
        'updated_at',
        'vendor_id'
    ];

    public function collection(): Collection
    {
        $rows ??= collect();
        $user ??= null;
        $userOrRedirect ??= null;
        $bills ??= collect();
        $category ??= '';
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
            $bills = Bill::where('created_by', $user->creatorId())->get();
            Log::info(__METHOD__ . ' fetched', [
                'count' => $bills->count(),
                'class' => static::class
            ]);
            $categoryModel = ProductServiceCategory::where('type', self::EXPENSE_TYPE)->first();
            $category = $categoryModel->name ?? '';
            $rowsArray = [];
            foreach ($bills as $bill) {
                foreach (self::UNSET_FIELDS as $f) {
                    unset($bill->{$f});
                }
                $rowsArray[] = [
                    $user->billNumberFormat((int)($bill->bill_id ?? 0)),
                    ($bill->bill_date ?? now())->format('Y-m-d'),
                    ($bill->due_date ?? now())->format('Y-m-d'),
                    $bill->order_no ?? '',
                    Bill::$statuses[$bill->status ?? 0] ?? '',
                    ($bill->send_date ?? now())->format('Y-m-d'),
                    $category
                ];
            }
            $rows = Collection::make($rowsArray);
            Log::info(__METHOD__ . ' built rows', [
                'count' => $rows->count(),
                'class' => static::class
            ]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => static::class
            ]);
            $rows = collect();
        }
        return $rows;
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e): void {
                try {
                    Log::info(__METHOD__ . ' styling started', [
                        'class' => static::class
                    ]);
                    $sheet = $e->sheet->getDelegate();
                    if (empty($sheet)) {
                        Log::warning(__METHOD__ . ' null sheet', [
                            'class' => static::class
                        ]);
                        return;
                    }
                    $sheet->getStyle(self::HEADER_ROW)->getFont()->setBold(true);
                    $sheet->freezePane(self::FREEZE_CELL);
                    $sheet->getStyle(self::HEADER_ROW)->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB(self::HEADER_FILL_COLOR);
                    $highest = $sheet->getHighestRow();
                    $lastCol = $sheet->getHighestColumn();
                    for ($r = 2; $r <= $highest; ++$r) {
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB(self::BODY_FILL_OPACITY);
                    }
                    $allRange = "A1:{$lastCol}{$highest}";
                    $sheet->getStyle($allRange)
                        ->getBorders()
                        ->getAllBorders()
                        ->getColor()
                        ->setARGB(self::BORDER_COLOR);
                    $headerRange = "A1:{$lastCol}1";
                    $sheet->getStyle($headerRange)
                        ->getBorders()
                        ->getAllBorders()
                        ->getColor()
                        ->setARGB(self::HEADER_BORDER_COLOR);
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
            $bills = Bill::where('created_by', $user->creatorId())->get();
            $categoryModel = ProductServiceCategory::where('type', self::EXPENSE_TYPE)->first();
            $category = $categoryModel->name ?? '';
            $data = [
                'bills' => $bills->map(fn($b) => [
                    'bill_id' => $user->billNumberFormat((int)($b->bill_id ?? 0)),
                    'bill_date' => ($b->bill_date ?? now())->format('Y-m-d'),
                    'due_date' => ($b->due_date ?? now())->format('Y-m-d'),
                    'order_no' => $b->order_no ?? '',
                    'status' => Bill::$statuses[$b->status ?? 0] ?? '',
                    'send_date' => ($b->send_date ?? now())->format('Y-m-d'),
                    'category' => $category,
                ])->toArray(),
                'currency_symbol' => self::_prepareCurrencySymbol($user),
                'headings' => self::HEADINGS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('bills');
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
