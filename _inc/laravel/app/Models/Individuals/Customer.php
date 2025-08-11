<?php

namespace App\Models;

use App\Config\Constants\{DatabaseConstants, PermissionsConstants};
use App\Traits\{ChecksLogin, UsesUuids};
use Carbon\Carbon;
use Illuminate\{Foundation\Auth\User as Authenticatable, Notifications\Notifiable};
use Illuminate\Support\Facades\{Auth, DB};
use Spatie\Permission\Traits\HasRoles;

class Customer extends Authenticatable
{
    use ChecksLogin, HasRoles, Notifiable, UsesUuids;

    public $settings;

    protected $guard_name = 'web';

    private const MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];
    private const FILLABLE = [
        'billing_address',
        'billing_city',
        'billing_country',
        'billing_name',
        'billing_phone',
        'billing_state',
        'billing_zip',
        'contact',
        DatabaseConstants::TABLE_CREATOR,
        'email',
        'email_verified_at',
        'avatar',
        'is_active',
        'lang',
        'name',
        'password',
        'proposal_prefix',          // ! ALERT check if needed
        'shipping_address',
        'shipping_city',
        'shipping_country',
        'shipping_name',
        'shipping_phone',
        'shipping_state',
        'shipping_zip',
        'tax_number',               // ! CHANGED added to match table
        'customer_id',
    ];
    protected $fillable = self::FILLABLE;
    private const COL_CUSTOMER_ID = 'customer_id';
    private const COL_ISSUE_DATE = 'issue_date';
    private const COL_STATUS     = 'status';
    private const COL_DUE_DATE   = 'due_date';

    protected $hidden = ['password', 'remember_token'];

    public function authId(): string
    {
        return $this->id;
    }

    public function creatorId(): string
    {
        return ($this->type === PermissionsConstants::CPN || $this->type === PermissionsConstants::SA)
            ? $this->id
            : $this->created_by;
    }

    public function currentLanguage(): string
    {
        return $this->lang;
    }

    public function currencySymbol(): string
    {
        $s = Utility::settings();
        return $s['site_currency_symbol'];
    }

    public function dateFormat(string $date): string
    {
        return date(Utility::settings()['site_date_format'], strtotime($date));
    }

    public function invoiceNumberFormat(int $n): string
    {
        return Utility::settings()['invoice_prefix'] . sprintf('%05d', $n);
    }

    public function priceFormat(float $price): string
    {
        $s = Utility::settings();
        $fmt = number_format($price, Utility::getValByName('decimal_number'));
        return ($s['site_currency_symbol_position'] === 'pre' ? $s['site_currency_symbol'] : '')
            . $fmt
            . ($s['site_currency_symbol_position'] === 'post' ? $s['site_currency_symbol'] : '');
    }

    public function proposalNumberFormat(int $n): string
    {
        return Utility::settings()['proposal_prefix'] . sprintf('%05d', $n);
    }

    public function timeFormat(string $time): string
    {
        return date(
            Utility::settings()['site_time_format'] ?? 'H:i:s',
            strtotime($time)
        );
    }

    public function invoiceChartData(): array
    {
        $userId  = Auth::id();
        $year    = date('Y');
        $today   = Carbon::today();
        $invoices = Invoice::where('customer_id', $userId)
            ->whereYear('send_date', $year)
            ->get();

        $data['month']      = array_map(fn (string $m): string => __($m), self::MONTHS);
        $data['currentYear'] = date('M-Y');

        $statusData = ['unpaid' => [], 'paid' => [], 'partial' => [], 'due' => []];
        foreach (self::MONTHS as $idx => $_) {
            $monthNum      = $idx + 1;
            $monthInvoices = $invoices->filter(
                fn ($inv): bool =>
                Carbon::parse($inv->send_date)->month === $monthNum
            );
            $statusData['unpaid'][] = (float) $monthInvoices
                ->filter(
                    fn ($inv): bool =>
                    $inv->status === 1 && Carbon::parse($inv->due_date)->gt($today)
                )
                ->sum(fn ($inv): float => $inv->getDue());
            $statusData['paid'][]   = (float) $monthInvoices
                ->filter(fn ($inv): bool => $inv->status === 4)
                ->sum(fn ($inv): float => $inv->getTotal());
            $statusData['partial'][] = (float) $monthInvoices
                ->filter(fn ($inv): bool => $inv->status === 3)
                ->sum(fn ($inv): float => $inv->getDue());
            $statusData['due'][]    = (float) $monthInvoices
                ->filter(
                    fn ($inv): bool =>
                    $inv->status === 1 && Carbon::parse($inv->due_date)->lt($today)
                )
                ->sum(fn ($inv): float => $inv->getDue());
        }
        $data['data'] = $statusData;

        $totalCount      = $invoices->count();
        $unpaidCount     = $invoices
            ->filter(
                fn ($inv): bool =>
                $inv->status === 1 && Carbon::parse($inv->due_date)->gt($today)
            )
            ->count();
        $paidCount       = $invoices->where('status', 4)->count();
        $partialCount    = $invoices->where('status', 3)->count();
        $dueCount        = $invoices
            ->filter(
                fn ($inv): bool =>
                $inv->status === 1 && Carbon::parse($inv->due_date)->lt($today)
            )
            ->count();

        $progressData = [
            'totalInvoice'        => $totalCount,
            'totalUnpaidInvoice'  => $unpaidCount,
            'totalPaidInvoice'    => $paidCount,
            'totalPartialInvoice' => $partialCount,
            'totalDueInvoice'     => $dueCount,
            'unpaidPr'            => $totalCount ? $unpaidCount * 100 / $totalCount : 0,
            'paidPr'              => $totalCount ? $paidCount * 100 / $totalCount : 0,
            'partialPr'           => $totalCount ? $partialCount * 100 / $totalCount : 0,
            'duePr'               => $totalCount ? $dueCount * 100 / $totalCount : 0,
            'unpaidColor'         => '#fc544b',
            'paidColor'           => '#63ed7a',
            'partialColor'        => '#6777ef',
            'dueColor'            => '#ffa426',
        ];

        $data['progressData'] = $progressData;
        return $data;
    }

    public function customerInvoice(string $customerId): \Illuminate\Database\Eloquent\Collection
    {
        return Invoice::where(self::COL_CUSTOMER_ID, $customerId)
            ->orderBy(self::COL_ISSUE_DATE, 'desc')
            ->get();
    }

    public function customerProposal(string $customerId): \Illuminate\Database\Eloquent\Collection
    {
        return Proposal::where(self::COL_CUSTOMER_ID, $customerId)
            ->orderBy(self::COL_ISSUE_DATE, 'desc')
            ->get();
    }

    public function customerOverdue(string $customerId): float
    {
        return Invoice::where(self::COL_CUSTOMER_ID, $customerId)
            ->whereNotIn(self::COL_STATUS, ['0', '4'])
            ->whereDate(self::COL_DUE_DATE, '<', now()->toDateString())
            ->get()
            ->sum(fn (Invoice $inv): float => $inv->getDue());
    }

    public function customerTotalInvoiceSum(string $customerId): float
    {
        return Invoice::where(self::COL_CUSTOMER_ID, $customerId)
            ->get()
            ->sum(fn (Invoice $inv): float => $inv->getTotal());
    }

    public function customerTotalInvoice(string $customerId): int
    {
        return Invoice::where(self::COL_CUSTOMER_ID, $customerId)
            ->count();
    }

    public static function customerId(string $customerName): int|string|\Illuminate\Http\RedirectResponse
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof \Illuminate\Http\RedirectResponse
        ) return $userOrRedirect;
        $user = $userOrRedirect;
        return DB::table('customers')
            ->where('name', $customerName)
            ->where(DatabaseConstants::TABLE_CREATOR, $user?->creatorId())
            ->value('id') ?? 0;
    }
}
