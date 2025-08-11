<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings};

final class CustomerExport implements FromCollection, WithHeadings
{
    use ChecksLogin;

    private const EXPORT_FIELDS = [
        'billing_address', 'billing_city', 'billing_country', 'billing_name',
        'billing_phone', 'billing_state', 'billing_zip', 'contact', 'customer_id',
        'email', 'name', 'shipping_address', 'shipping_city', 'shipping_country',
        'shipping_name', 'shipping_phone', 'shipping_state', 'shipping_zip', 'balance'
    ];
    private const HEADINGS     = [
        'Customer No', 'Name', 'Email', 'Contact', 'Billing Name',
        'Billing Country', 'Billing State', 'Billing City', 'Billing Phone',
        'Billing Zip', 'Billing Address', 'Shipping Name', 'Shipping Country',
        'Shipping State', 'Shipping City', 'Shipping Phone', 'Shipping Zip',
        'Shipping Address', 'Balance'
    ];

    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;
        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id]);

        $rows = Customer::where(
            'created_by',
            $user?->creatorId()
        )
            ->get(self::EXPORT_FIELDS)
            ->map(fn ($c) => [
                $user?->customerNumberFormat($c->customer_id),
                $c->name,
                $c->email,
                $c->contact,
                $c->billing_name,
                $c->billing_country,
                $c->billing_state,
                $c->billing_city,
                $c->billing_phone,
                $c->billing_zip,
                $c->billing_address,
                $c->shipping_name,
                $c->shipping_country,
                $c->shipping_state,
                $c->shipping_city,
                $c->shipping_phone,
                $c->shipping_zip,
                $c->shipping_address,
                $user?->priceFormat($c->balance)
            ]);

        Log::info(__METHOD__ . ' completed', ['count' => $rows->count()]);
        return $rows;
    }

    public function headings(): array
    {
        return self::HEADINGS;
    }
}
