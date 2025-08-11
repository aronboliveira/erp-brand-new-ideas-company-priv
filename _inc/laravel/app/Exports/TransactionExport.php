<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};

final class TransactionExport implements FromCollection, WithHeadings
{
    use ChecksLogin;

    private const REMOVED_FIELDS = [
        'created_by', 'created_at', 'updated_at',
        'user_type', 'user_id', 'payment_id'
    ];
    private const HEADINGS = [
        'Transaction Id', 'Account', 'Type',
        'Amount', 'Description', 'Date', 'Category'
    ];

    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id]);

        $transactions = Transaction::where(
            'created_by',
            $user?->id
        )->get();
        Log::info(__METHOD__ . ' fetched', ['count' => $transactions->count()]);

        $transactions->each(function ($t) {
            foreach (self::REMOVED_FIELDS as $f) unset($t->$f);
            $t->account = Transaction::accounts($t->account);
        });
        Log::info(__METHOD__ . ' formatted', ['count' => $transactions->count()]);

        return $transactions;
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }
}
