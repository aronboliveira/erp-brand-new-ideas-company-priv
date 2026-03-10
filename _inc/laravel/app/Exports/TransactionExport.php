<?php

namespace App\Exports;
use App\Models\Transaction;
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};
final class TransactionExport implements FromCollection, WithHeadings
{
    use ChecksLogin;
    use DelegatesPythonExport;
    private const HEADINGS = [
        'Transaction Id',
        'Account',
        'Type',
        'Amount',
        'Description',
        'Date',
        'Category'
    ];
    private const PYTHON_EXPORTER = 'TransactionExport';
    private const REMOVED_FIELDS  = [
        'created_by',
        'created_at',
        'updated_at',
        'user_type',
        'user_id',
        'payment_id'
    public function collection(): Collection
    {
        try {
            $userOrRedirect = self::_checkLogin();
            if ($userOrRedirect instanceof RedirectResponse) {
                Log::warning(__METHOD__ . ' auth redirect', [
                    'class' => static::class
                ]);
                return collect();
            }
            $user = $userOrRedirect;
            Log::info(__METHOD__ . ' started', [
                'user_id' => $user->id ?? null,
                'class' => static::class
            ]);
            $transactions = Transaction::where(
                'created_by',
                $user->id ?? null
            )->get();
            Log::info(__METHOD__ . ' fetched', [
                'count' => $transactions->count(),
            $transactions->each(function ($t) {
                foreach (self::REMOVED_FIELDS as $f) {
                    unset($t->$f);
                }
                $t->account = !empty($t->account)
                    ? Transaction::accounts((string) $t->account)
                    : '';
            });
            Log::info(__METHOD__ . ' formatted', [
            return $transactions;
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            return collect();
        }
    }
    public function headings(): array
        return self::HEADINGS;
    public function exportViaPython(?string $outputPath = null): string
        $result ??= '';
        $data ??= [];
            $collection = $this->collection();
            $data = [
                'transactions' => $collection->toArray(),
                'headings' => self::HEADINGS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('transaction');
            $result = self::_executePythonExporter(
                self::PYTHON_EXPORTER,
                $data,
                $outputPath
            );
            $result = '';
        return $result;
}
