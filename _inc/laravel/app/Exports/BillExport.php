<?php

namespace App\Exports;
use App\Models\{Bill, ProductServiceCategory};
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
final class BillExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin, DelegatesPythonExport;
    private const BODY_FILL_OPACITY  = '33FFFFFF';
    private const BORDER_COLOR       = '808080';
    private const EXPENSE_TYPE       = 'expense';
    private const HEADER_BORDER_COLOR = '000000';
    private const HEADER_FILL_COLOR  = 'FF1F1F2F';
    private const FREEZE_CELL        = 'A2';
    private const HEADER_ROW         = '1:1';
    private const UNSET_FIELDS = [
        'created_at', 'created_by', 'customer_id', 'discount_apply',
        'id', 'shipping_display', 'updated_at', 'vendor_id'
    ];
    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id]);
        $bills = Bill::where(
            'created_by',
            $user?->creatorId()
        )->get();
        Log::info(__METHOD__ . ' fetched', ['count' => $bills->count()]);
        $category = ProductServiceCategory::where(
            'type',
            self::EXPENSE_TYPE
        )->first()->name ?? '';
        $rows = [];
        foreach ($bills as $bill) {
            foreach (self::UNSET_FIELDS as $f) unset($bill->$f);
            /** @var \Carbon\Carbon|null $billDate */
            $billDate = $bill->bill_date;
            /** @var \Carbon\Carbon|null $dueDate */
            $dueDate = $bill->due_date;
            /** @var \Carbon\Carbon|null $sendDate */
            $sendDate = $bill->send_date;
            $rows[] = [
                $user?->billNumberFormat($bill->bill_id),
                $billDate?->format('Y-m-d') ?? '',
                $dueDate?->format('Y-m-d') ?? '',
                $bill->order_no ?? '',
                Bill::$statuses[$bill->status] ?? '',
                $sendDate?->format('Y-m-d') ?? '',
                $category
            ];
        }
        Log::info(__METHOD__ . ' built rows', ['count' => count($rows)]);
        return Collection::make($rows);
    }
    public function headings(): array
        return [
            'Bill No', 'Bill Date', 'Due Date',
            'Order No', 'Status', 'Send Date', 'Category'
        ];
    public function registerEvents(): array
            AfterSheet::class => function (AfterSheet $e): void {
                Log::info(__METHOD__ . ' styling started');
                $sheet  = $e->sheet->getDelegate();
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
                    ->setARGB(self::HEADER_BORDER_COLOR);
                Log::info(__METHOD__ . ' styling completed');
            }
}
