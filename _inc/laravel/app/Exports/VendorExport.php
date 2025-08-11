<?php

namespace App\Exports;

use App\Traits\ChecksLogin;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Log};
use Maatwebsite\Excel\Concerns\{FromCollection, WithEvents, WithHeadings};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\{Border, Fill};

final class VendorExport implements FromCollection, WithHeadings, WithEvents
{
    use ChecksLogin;

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

    private const REMOVED_FIELDS = [
        'id',
        'password',
        'lang',
        'tax_number',
        'is_active',
        'avatar',
        'created_by',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at'
    ];

    private int $projectId; // unused but placeholder if needed

    public function collection(): Collection
    {
        if (
            ($userOrRedirect = self::_checkLogin())
            instanceof RedirectResponse
        ) return collect();
        $user = $userOrRedirect;

        Log::info(__METHOD__ . ' started', ['user_id' => $user?->id]);

        $vendors = Vendor::where(
            'created_by',
            $user?->creatorId()
        )->get();

        $rows = $vendors->map(function ($vendor) use ($user) {
            foreach (self::REMOVED_FIELDS as $field) unset($vendor->{$field});

            return [
                'id'               => $user?->vendorNumberFormat($vendor->vendor_id),
                'name'             => $vendor->name,
                'email'            => $vendor->email,
                'contact'          => $vendor->contact,
                'billingName'      => $vendor->billing_name,
                'billingCountry'   => $vendor->billing_country,
                'billingState'     => $vendor->billing_state,
                'billingCity'      => $vendor->billing_city,
                'billingPhone'     => $vendor->billing_phone,
                'billingZip'       => $vendor->billing_zip,
                'billingAddress'   => $vendor->billing_address,
                'shippingName'     => $vendor->shipping_name,
                'shippingCountry'  => $vendor->shipping_country,
                'shippingState'    => $vendor->shipping_state,
                'shippingCity'     => $vendor->shipping_city,
                'shippingPhone'    => $vendor->shipping_phone,
                'shippingZip'      => $vendor->shipping_zip,
                'shippingAddress'  => $vendor->shipping_address,
                'balance'          => $user?->priceFormat($vendor->balance),
            ];
        });

        Log::info(__METHOD__ . ' completed', ['count' => $rows->count()]);

        return $rows;
    }

    public function headings(): array
    {
        return self::HEADERS;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                // style header row
                $sheet->getStyle('A1:S1')->applyFromArray([
                    'font'   => ['bold' => true],
                    'fill'   => [
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
                ]);

                $sheet->freezePane('A2');
            }
        ];
    }
}
