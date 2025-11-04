<?php

namespace App\Models;

use App\Config\Constants\{PermissionsConstants, SettingsConstants as SC};
use App\Traits\UsesUuids;
use Illuminate\{
    Database\Eloquent\Collection,
    Foundation\Auth\User as Authenticatable,
    Notifications\Notifiable,
    Support\Facades\Auth
};
use Spatie\Permission\Traits\HasRoles;

class Vendor extends Authenticatable
{
    use HasRoles;
    use Notifiable;
    use UsesUuids;

    private const FILLABLE = [
        'vendor_id',
        'name',
        'email',
        'password',
        'contact',
        'avatar',
        'is_active',
        'created_by',
        'email_verified_at',
        'billing_name',
        'billing_country',
        'billing_state',
        'billing_city',
        'billing_phone',
        'billing_zip',
        'billing_address',
        'shipping_name',
        'shipping_country',
        'shipping_state',
        'shipping_city',
        'shipping_phone',
        'shipping_zip',
        'shipping_address',
        'tax_number',
        'lang',
        'balance'
    ];
    protected $fillable = self::FILLABLE;

    private const MONTHS = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
    ];

    public function authId(): string|int
    {
        return $this->id;
    }

    public function creatorId(): string|int
    {
        return in_array($this->type, [
            PermissionsConstants::CPN,
            PermissionsConstants::SA
        ])
            ? $this->id
            : $this->created_by;
    }

    public function currentLanguage(): string
    {
        return $this->lang;
    }

    public function priceFormat(float|int $price): string
    {
        $s = Utility::settings();
        return ($s[SC::CR_SB_P] === 'pre'
            ? $s[SC::CR_SB] : '')
            . number_format($price, $s['decimal_number'])
            . ($s[SC::CR_SB_P] === 'post'
                ? $s[SC::CR_SB] : '');
    }

    public function currencySymbol(): string
    {
        return Utility::settings()[SC::CR_SB] ?? '';
    }

    public function dateFormat(string $date): string
    {
        return date(
            Utility::settings()[SC::DT_FM] ?? 'Y-m-d',
            strtotime($date)
        );
    }

    public function timeFormat(string $time): string
    {
        return date(
            Utility::settings()[SC::TM_FM] ?? 'H:i:s',
            strtotime($time)
        );
    }

    public function invoiceNumberFormat(int $num): string
    {
        return Utility::settings()[SC::INV_PFX]
            . sprintf("%05d", $num);
    }

    public function purchaseNumberFormat(int $num): string
    {
        return Utility::settings()[SC::PRC_PFX]
            . sprintf("%05d", $num);
    }

    public function billNumberFormat(int $num): string
    {
        return Utility::settings()[SC::BL_PFX]
            . sprintf("%05d", $num);
    }

    public function billChartData(): array
    {
        $data['month'] = self::MONTHS;
        $data['currentYear'] = date('M-Y');
        $user = Auth::user();
        foreach (self::MONTHS as $i => $m) {
            $i++;
            $unpaid = Bill::where('vendor_id', $user?->id)
                ->whereYear('send_date', date('Y'))
                ->whereMonth('send_date', $i)
                ->where('status', '1')
                ->where('due_date', '>', date('Y-m-d'))
                ->get()
                ->sum(fn($b) => $b->getDue());
            $paid = Bill::where('vendor_id', $user?->id)
                ->whereYear('send_date', date('Y'))
                ->whereMonth('send_date', $i)
                ->where('status', '4')
                ->get()
                ->sum(fn($b) => $b->getTotal());
            $partial = Bill::where('vendor_id', $user?->id)
                ->whereYear('send_date', date('Y'))
                ->whereMonth('send_date', $i)
                ->where('status', '3')
                ->get()
                ->sum(fn($b) => $b->getDue());
            $due = Bill::where('vendor_id', $user?->id)
                ->whereYear('send_date', date('Y'))
                ->whereMonth('send_date', $i)
                ->where('status', '1')
                ->where('due_date', '<', date('Y-m-d'))
                ->get()
                ->sum(fn($b) => $b->getDue());
            $data['data']['unpaid'][] = $unpaid;
            $data['data']['paid'][]   = $paid;
            $data['data']['partial'][] = $partial;
            $data['data']['due'][]    = $due;
        }
        $total = Bill::where('vendor_id', $user?->id)
            ->whereYear('send_date', date('Y'))
            ->count();
        foreach (['unpaid', 'paid', 'partial', 'due'] as $k) {
            $cnt = count($data['data'][$k]);
            $data['progressData']["total{$k}Bill"] = $cnt;
            $data['progressData']["{$k}Pr"] = $total
                ? ($cnt * 100) / $total
                : 0;
        }
        return $data;
    }

    public function vendorBill(int $vendorId): Collection
    {
        return Bill::where('vendor_id', $vendorId)
            ->orderBy('bill_date', 'desc')
            ->get();
    }

    public function vendorOverdue(int $vendorId): float|int
    {
        return Bill::where('vendor_id', $vendorId)
            ->whereNotIn('status', ['0', '4'])
            ->where('due_date', '<', date('Y-m-d'))
            ->get()
            ->sum(fn($b) => $b->getDue());
    }

    public function vendorTotalBill(int $vendorId): int
    {
        return Bill::where('vendor_id', $vendorId)->count();
    }

    public function vendorTotalBillSum(int $vendorId): float|int
    {
        return Bill::where('vendor_id', $vendorId)
            ->get()
            ->sum(fn($b) => $b->getTotal());
    }
}
