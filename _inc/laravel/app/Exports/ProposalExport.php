<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use App\Models\{Proposal, ProductServiceCategory};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};

final class ProposalExport implements FromCollection, WithHeadings
{
    use ChecksLogin;

    private const REMOVED_FIELDS = [
        'created_by', 'customer_id', 'converted_invoice_id', 'discount_apply',
        'is_convert', 'created_at', 'updated_at'
    ];
    private const HEADINGS = [
        'ID', 'Proposal No', 'Issue Date', 'Send Date', 'Category', 'Status'
    ];
    private const EXPENSE_TYPE = 'income';

    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id]);

        $items = Proposal::where(
            'created_by',
            $user?->creatorId()
        )->get();
        Log::info(__METHOD__ . ' fetched', ['count' => $items->count()]);

        $category = ProductServiceCategory::where(
            'type',
            self::EXPENSE_TYPE
        )->first()->name ?? '';
        $rows = [];

        foreach ($items as $item) {
            foreach (self::REMOVED_FIELDS as $f) unset($item->$f);
            $rows[] = [
                $item->id,
                $user?->proposalNumberFormat($item->proposal_id),
                $item->issue_date?->format('Y-m-d') ?? '',
                $item->send_date?->format('Y-m-d') ?? '',
                $category,
                Proposal::$statuses[$item->status] ?? ''
            ];
        }
        Log::info(__METHOD__ . ' formatted', ['count' => count($rows)]);

        return Collection::make($rows);
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }
}
