<?php

namespace App\Exports;
use App\Models\Vendor;
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\{Border, Fill};
final class VendorExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;
    use DelegatesPythonExport;
    private const HEADERS = [
        'ID',
        'Name',
        'Email',
        'Contact',
        'Billing Name',
        'Billing Country',
        'Billing State',
        'Billing City',
        'Billing Phone',
        'Billing Zip',
        'Billing Address',
        'Shipping Name',
        'Shipping Country',
        'Shipping State',
        'Shipping City',
        'Shipping Phone',
        'Shipping Zip',
        'Shipping Address',
        'Balance'
    ];
    private const PYTHON_EXPORTER = 'VendorExport';
    private const REMOVED_FIELDS = [
        'avatar',
        'created_at',
        'created_by',
        'email_verified_at',
        'id',
        'is_active',
        'lang',
        'password',
        'remember_token',
        'tax_number',
        'updated_at'
    private int $projectId;
    public function collection(): Collection
    {
        $rows ??= collect();
        $user ??= null;
        $userOrRedirect ??= null;
        $vendors ??= collect();
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
            Log::info(__METHOD__ . ' started', [
                'user_id' => $user->id ?? null,
                'class' => static::class
            ]);
            $vendors = Vendor::where('created_by', $user->creatorId())->get();
            $rows = $vendors->map(function ($vendor) use ($user) {
                foreach (self::REMOVED_FIELDS as $field) {
                    unset($vendor->{$field});
                }
                return [
                    'id' => $user->vendorNumberFormat((int)($vendor->vendor_id ?? 0)),
                    'name' => $vendor->name ?? '',
                    'email' => $vendor->email ?? '',
                    'contact' => $vendor->contact ?? '',
                    'billingName' => $vendor->billing_name ?? '',
                    'billingCountry' => $vendor->billing_country ?? '',
                    'billingState' => $vendor->billing_state ?? '',
                    'billingCity' => $vendor->billing_city ?? '',
                    'billingPhone' => $vendor->billing_phone ?? '',
                    'billingZip' => $vendor->billing_zip ?? '',
                    'billingAddress' => $vendor->billing_address ?? '',
                    'shippingName' => $vendor->shipping_name ?? '',
                    'shippingCountry' => $vendor->shipping_country ?? '',
                    'shippingState' => $vendor->shipping_state ?? '',
                    'shippingCity' => $vendor->shipping_city ?? '',
                    'shippingPhone' => $vendor->shipping_phone ?? '',
                    'shippingZip' => $vendor->shipping_zip ?? '',
                    'shippingAddress' => $vendor->shipping_address ?? '',
                    'balance' => $user->priceFormat($vendor->balance ?? 0),
                ];
            });
            Log::info(__METHOD__ . ' completed', [
                'count' => $rows->count(),
        } catch (\Throwable $e) {
            Log::error(__METHOD__ . ' exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            $rows = collect();
        }
        return $rows;
    }
    public function headings(): array
        return self::HEADERS;
    public function registerEvents(): array
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                try {
                    Log::info(__METHOD__ . ' styling started', [
                        'class' => static::class
                    ]);
                    $sheet = $event->sheet->getDelegate();
                    if (empty($sheet)) {
                        Log::warning(__METHOD__ . ' null sheet', [
                            'class' => static::class
                        ]);
                        return;
                    }
                    $sheet->getStyle('A1:S1')->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => '001122']
                        ],
                        'borders' => [
                            'horizontal' => [
                                'borderStyle' => Border::BORDER_HAIR,
                                'color' => ['argb' => '11333333']
                            ],
                            'vertical' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FF333333']
                            ]
                        ]
                    $sheet->freezePane('A2');
                    Log::info(__METHOD__ . ' styling completed', [
                } catch (\Throwable $e) {
                    Log::error(__METHOD__ . ' styling exception', [
                        'error' => $e->getMessage(),
        ];
    public function exportViaPython(?string $outputPath = null): string
        $result ??= '';
        $data ??= [];
                return '';
            $data = [
                'vendors' => $vendors->map(fn($v) => [
                    'vendor_id' => $user->vendorNumberFormat((int)($v->vendor_id ?? 0)),
                    'name' => $v->name ?? '',
                    'email' => $v->email ?? '',
                    'contact' => $v->contact ?? '',
                    'billing_name' => $v->billing_name ?? '',
                    'billing_country' => $v->billing_country ?? '',
                    'billing_state' => $v->billing_state ?? '',
                    'billing_city' => $v->billing_city ?? '',
                    'billing_phone' => $v->billing_phone ?? '',
                    'billing_zip' => $v->billing_zip ?? '',
                    'billing_address' => $v->billing_address ?? '',
                    'shipping_name' => $v->shipping_name ?? '',
                    'shipping_country' => $v->shipping_country ?? '',
                    'shipping_state' => $v->shipping_state ?? '',
                    'shipping_city' => $v->shipping_city ?? '',
                    'shipping_phone' => $v->shipping_phone ?? '',
                    'shipping_zip' => $v->shipping_zip ?? '',
                    'shipping_address' => $v->shipping_address ?? '',
                    'balance' => $v->balance ?? 0,
                ])->toArray(),
                'currency_symbol' => self::_prepareCurrencySymbol($user),
                'headings' => self::HEADERS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('vendors');
            $result = self::_executePythonExporter(
                self::PYTHON_EXPORTER,
                $data,
                $outputPath
            );
            $result = '';
        return $result;
}
