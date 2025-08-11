<?php

namespace App\Models;

use App\Traits\{ChecksLogin, UsesUuids};
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use ChecksLogin, UsesUuids;

    private const GOAL_TYPES = [ // ! CHANGED
        'Invoice',
        'Bill',
        'Revenue',
        'Payment',
    ];

    protected $fillable = [
        'name',
        'type',
        'from',
        'to',
        'amount',
        'is_display',
        'created_by',
    ];

    protected $casts = [ // * cast for consistency
        'amount'     => 'decimal:2',
        'is_display' => 'boolean',
    ];

    public function target(string $type, string $from, string $to, float $amount): array
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        )
            return $userOrRedirect;
        $user = $userOrRedirect;
        $start  = $from . '-00';
        $end    = $to   . '-00';
        $userId = $user?->creatorId();
        $total  = 0;

        match ($type) {
            self::GOAL_TYPES[0] => $total = Invoice::where('created_by', $userId)
                ->whereBetween('issue_date', [$start, $end])
                ->get()
                ->sum(fn ($inv) => $inv->getTotal()),
            self::GOAL_TYPES[1] => $total = Bill::where('created_by', $userId)
                ->whereBetween('bill_date', [$start, $end])
                ->get()
                ->sum(fn ($b) => $b->getTotal()),
            self::GOAL_TYPES[2] => $total = Revenue::where('created_by', $userId)
                ->whereBetween('date', [$start, $end])
                ->sum('amount'),
            self::GOAL_TYPES[3] => $total = Payment::where('created_by', $userId)
                ->whereBetween('date', [$start, $end])
                ->sum('amount'),
            default            => $total = 0,
        };

        return [
            'percentage' => $amount > 0 ? ($total * 100) / $amount : 0,
            'total'      => $total,
        ];
    }
}
