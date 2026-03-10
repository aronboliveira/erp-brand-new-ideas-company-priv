<?php

namespace App\Exports;

use App\Models\{Customer, Invoice, ProductServiceCategory};
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};

final class InvoiceExport implements FromCollection, WithHeadings
{
    use ChecksLogin;
    use DelegatesPythonExport;

    private const HEADERS = [
        'Invoice Id',
        'Issue Date',
        'Due Date',
        'Send Date',
        'Category',
        'Ref Number',
        'Status'
    ];
    private const PYTHON_EXPORTER = 'InvoiceExport';
    private const UNSET_FIELDS = [
        'created_at',
        'created_by',
        'customer_id',
        'discount_apply',
        'id',
        'shipping_display',
        'updated_at'
    ];

    public function collection(): Collection
    {
        $export ??= collect();
        $user ??= null;
        $userOrRedirect ??= null;
        $invoices ??= collect();
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
            $invoices = Invoice::where('created_by', $user->creatorId())->get();
            $export = collect();
            $categoryModel = ProductServiceCategory::where('type', 'income')->first();
            $categoryName = $categoryModel->name ?? '';
            foreach ($invoices as $invoice) {
                foreach (self::UNSET_FIELDS as $field) {
                    unset($invoice->{$field});
                }
                $invoice->invoice_id = $user->invoiceNumberFormat((int)($invoice->invoice_id ?? 0));
                $invoice->category_id = $categoryName;
                // status is already resolved via getStatusAttribute accessor
                $export->push($invoice);
            }
            Log::info(__METHOD__ . ' completed', [
                'count' => $export->count(),
                'class' => static::class
            ]);
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class' => static::class
            ]);
            $export = collect();
        }
        return $export;
    }

    public function headings(): array
    {
        return self::HEADERS;
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
            $invoices = Invoice::where('created_by', $user->creatorId())->get();
            $categoryModel = ProductServiceCategory::where('type', 'income')->first();
            $categoryName = $categoryModel->name ?? '';
            $data = [
                'rows' => $invoices->map(fn($i) => [
                    'invoice_id' => $user->invoiceNumberFormat((int)($i->invoice_id ?? 0)),
                    'issue_date' => ($i->issue_date ?? now())->format('Y-m-d'),
                    'due_date' => ($i->due_date ?? now())->format('Y-m-d'),
                    'send_date' => ($i->send_date ?? now())->format('Y-m-d'),
                    'category' => $categoryName,
                    'ref_number' => $i->ref_number ?? '',
                    'status' => $i->status ?? '',
                ])->toArray(),
                'currency_symbol' => self::_prepareCurrencySymbol($user),
                'headings' => self::HEADERS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('invoices');
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
