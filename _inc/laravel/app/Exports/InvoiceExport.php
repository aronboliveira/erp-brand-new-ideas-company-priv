<?php

namespace App\Exports;
use App\Models\{Customer, Invoice, ProductServiceCategory};
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};
final class InvoiceExport implements FromCollection, WithHeadings
{
    use ChecksLogin, DelegatesPythonExport;
    private const HEADERS     = [
        'Invoice Id', 'Issue Date', 'Due Date', 'Send Date',
        'Category', 'Ref Number', 'Status'
    ];
    private const UNSET_FIELDS = [
        'id', 'customer_id', 'created_by', 'shipping_display',
        'discount_apply', 'created_at', 'updated_at'
    public function collection(): Collection
    {
        // guard & logging
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id]);
        $invoices = Invoice::where(
            'created_by',
            $user?->creatorId()
        )->get();
        $export = collect();
        foreach ($invoices as $invoice) {
            foreach (self::UNSET_FIELDS as $field) unset($invoice->$field);
            /** @phpstan-ignore assign.propertyType */
            $invoice->invoice_id = $user?->invoiceNumberFormat($invoice->invoice_id);
            // $invoice->customer_id = $user?->customerNumberFormat($invoice->customer_id);
            $invoice->category_id = ProductServiceCategory::where(
                'type',
                'income'
            )->first()->name;
            $invoice->status     = Invoice::$statuses[$invoice->status] ?? '';
            $export->push($invoice);
        }
        Log::info(__METHOD__ . ' completed', ['count' => $export->count()]);
        return $export;
    }
    public function headings(): array
        return self::HEADERS;
}
