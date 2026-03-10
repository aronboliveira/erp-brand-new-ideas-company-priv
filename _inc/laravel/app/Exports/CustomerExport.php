<?php

namespace App\Exports;
use App\Models\Customer;
use App\Traits\ChecksLogin;
use App\Traits\DelegatesPythonExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};
final class CustomerExport implements FromCollection, WithHeadings
{
    use ChecksLogin;
    use DelegatesPythonExport;
    private const EXPORT_FIELDS = [
        'balance',
        'billing_address',
        'billing_city',
        'billing_country',
        'billing_name',
        'billing_phone',
        'billing_state',
        'billing_zip',
        'contact',
        'customer_id',
        'email',
        'name',
        'shipping_address',
        'shipping_city',
        'shipping_country',
        'shipping_name',
        'shipping_phone',
        'shipping_state',
        'shipping_zip'
    ];
    private const HEADINGS = [
        'Customer No',
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
    private const PYTHON_EXPORTER = 'CustomerExport';
    public function collection(): Collection
    {
        $rows ??= collect();
        $user ??= null;
        $userOrRedirect ??= null;
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
            $rows = Customer::where('created_by', $user->creatorId())
                ->get(self::EXPORT_FIELDS)
                ->map(fn($c) => [
                    $user->customerNumberFormat((int)($c->customer_id ?? 0)),
                    $c->name ?? '',
                    $c->email ?? '',
                    $c->contact ?? '',
                    $c->billing_name ?? '',
                    $c->billing_country ?? '',
                    $c->billing_state ?? '',
                    $c->billing_city ?? '',
                    $c->billing_phone ?? '',
                    $c->billing_zip ?? '',
                    $c->billing_address ?? '',
                    $c->shipping_name ?? '',
                    $c->shipping_country ?? '',
                    $c->shipping_state ?? '',
                    $c->shipping_city ?? '',
                    $c->shipping_phone ?? '',
                    $c->shipping_zip ?? '',
                    $c->shipping_address ?? '',
                    $user->priceFormat($c->balance ?? 0)
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
        return self::HEADINGS;
    public function exportViaPython(?string $outputPath = null): string
        $result ??= '';
        $data ??= [];
                return '';
            $customers = Customer::where('created_by', $user->creatorId())
                ->get(self::EXPORT_FIELDS);
            $data = [
                'customers' => $customers->map(fn($c) => [
                    'customer_id' => $user->customerNumberFormat((int)($c->customer_id ?? 0)),
                    'name' => $c->name ?? '',
                    'email' => $c->email ?? '',
                    'contact' => $c->contact ?? '',
                    'billing_name' => $c->billing_name ?? '',
                    'billing_country' => $c->billing_country ?? '',
                    'billing_state' => $c->billing_state ?? '',
                    'billing_city' => $c->billing_city ?? '',
                    'billing_phone' => $c->billing_phone ?? '',
                    'billing_zip' => $c->billing_zip ?? '',
                    'billing_address' => $c->billing_address ?? '',
                    'shipping_name' => $c->shipping_name ?? '',
                    'shipping_country' => $c->shipping_country ?? '',
                    'shipping_state' => $c->shipping_state ?? '',
                    'shipping_city' => $c->shipping_city ?? '',
                    'shipping_phone' => $c->shipping_phone ?? '',
                    'shipping_zip' => $c->shipping_zip ?? '',
                    'shipping_address' => $c->shipping_address ?? '',
                    'balance' => $c->balance ?? 0,
                ])->toArray(),
                'currency_symbol' => self::_prepareCurrencySymbol($user),
                'headings' => self::HEADINGS,
            ];
            if (empty($outputPath)) {
                $outputPath = self::_generateOutputPath('customers');
            $result = self::_executePythonExporter(
                self::PYTHON_EXPORTER,
                $data,
                $outputPath
            );
            $result = '';
        return $result;
}
