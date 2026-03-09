<?php

namespace App\Exports;

use App\Models\{ProductServiceCategory, Proposal};
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};

final class ProposalExport implements FromCollection, WithHeadings
{
    use ChecksLogin;
    use DelegatesPythonExport;

    private const EXPENSE_TYPE = 'income';
    private const HEADINGS = [
        'ID',
        'Proposal No',
        'Issue Date',
        'Send Date',
        'Category',
        'Status'
    ];
    private const PYTHON_EXPORTER = 'ProposalExport';
    private const REMOVED_FIELDS = [
        'converted_invoice_id',
        'created_at',
        'created_by',
        'customer_id',
        'discount_apply',
        'is_convert',
        'updated_at'
    ];

    public function collection(): Collection
    {
        $rows ??= collect();
        $user ??= null;
        $userOrRedirect ??= null;
        $items ??= collect();
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
            $items = Proposal::where('created_by', $user->creatorId())->get();
            Log::info(__METHOD__ . ' fetched', [
                'count' => $items->count(),
                'class' => static::class
            ]);
            $categoryModel = ProductServiceCategory::where('type', self::EXPENSE_TYPE)->first();
            $category = $categoryModel->name ?? '';
            $rowsArray = [];
            foreach ($items as $item) {
                foreach (self::REMOVED_FIELDS as $f) {
                    unset($item->{$f});
                }
                $rowsArray[] = [
                    $item->id ?? 0,
                    $user->proposalNumberFormat((int)($item->proposal_id ?? 0)),
                    ($item->issue_date ?? now())->format('Y-m-d'),
                    ($item->send_date ?? now())->format('Y-m-d'),
                    $category,
                    Proposal::$statuses[$item->status ?? 0] ?? ''
                ];
            }
            $rows = Collection::make($rowsArray);
            Log::info(__METHOD__ . ' formatted', [
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
            $proposals = Proposal::where('created_by', $user->creatorId())->get();
            $categoryModel = ProductServiceCategory::where('type', self::EXPENSE_TYPE)->first();
            $category = $categoryModel->name ?? '';
            $data = [
                'proposals' => $proposals->map(fn($p) => [
                    'id' => $p->id ?? 0,
                    'proposal_no' => $user->proposalNumberFormat((int)($p->proposal_id ?? 0)),
                    'issue_date' => ($p->issue_date ?? now())->format('Y-m-d'),
                    'send_date' => ($p->send_date ?? now())->format('Y-m-d'),
                    'category' => $category,
                    'status' => Proposal::$statuses[$p->status ?? 0] ?? '',
                ])->toArray(),
                'currency_symbol' => self::_prepareCurrencySymbol($user),
                'headings' => self::HEADINGS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('proposals');
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
