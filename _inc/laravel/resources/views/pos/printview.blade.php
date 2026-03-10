@php
    use App\Config\Constants\{
        SettingsConstants,
        ViewClassNamesConstants as VC
    };
    use App\Models\Utility;
    use Illuminate\Support\Facades\Auth;

    $user     = Auth::user();
    $lang     = Utility::fetchUserLang(user: $user);
    $settings = Utility::settings();

    $posId          = data_get($details, 'pos_id', '');
    $dateOfPos      = data_get($details, 'date', '');
    $customer       = data_get($details, 'customer', []);
    $custDetails    = data_get($customer, 'details', '');
    $custName       = data_get($customer, 'name', '');
    $custAddress    = data_get($customer, 'address', '');
    $custEmail      = data_get($customer, 'email', '');
    $custPhone      = data_get($customer, 'phone_number', '');

    $warehouse      = data_get($details, 'warehouse', []);
    $warehouseDet   = data_get($warehouse, 'details', '');

    $items          = data_get($sales, 'data', []);
    $hasItems       = is_array($items) && count($items) > 0;

    $discount       = data_get($sales, 'discount', 0);
    $total          = data_get($sales, 'total', 0);
@endphp

<div class="pt-0 pb-3 modal-body pos-module" id="printarea">
    <table class="table pos-module-tbl">
        <tbody>
            <div class="text-center">
                <h3>{{ !empty($settings['company_name']) ? $settings['company_name'] : __('No company value available') }}</h3>
            </div>
            <br>
            <div class="text-left">
                <b>{{ $posId }}</b>
            </div>
            <div class="text-left">
                {{ !empty($settings['company_name']) ? $settings['company_name'] : __('No company value available') }}<br>
                {{ !empty($settings['mail_from_address']) ? $settings['mail_from_address'] : __('No email value available') }}<br>
                {{ !empty($settings['company_address']) ? $settings['company_address'] : __('No address value available') }}<br>
                {{ !empty($settings['company_city']) ? $settings['company_city'] : __('No city value available') }},
                {{ !empty($settings['company_state']) ? $settings['company_state'] : __('No state value available') }},
                {{ !empty($settings['company_zipcode']) ? $settings['company_zipcode'] : __('No zipcode value available') }}<br>
                {{ !empty($settings['company_country']) ? $settings['company_country'] : __('No country value available') }}<br>
                {{ !empty($settings['company_telephone']) ? $settings['company_telephone'] : __('No telephone value available') }}<br>
            </div>
            <div class="invoice-to mt-2 product-border">
                {!! empty($custName) ? ($custDetails ?: '') : '' !!}
            </div>
            <br>
            <div>{!! !empty($custName) ? 'Name:  ' . e($custName) : __('No custom name available') !!}</div>
            <div>{!! !empty($custAddress) ? 'Address:  ' . e($custAddress) : __('No address value available') !!}</div>
            <div>{!! !empty($custEmail) ? 'Email:  ' . e($custEmail) : __('No email value available') !!}</div>
            <div>{!! !empty($custPhone) ? 'Phone:  ' . e($custPhone) : __('No phone value available') !!}</div>
            <div>{!! !empty($dateOfPos) ? 'Date of POS:  ' . e($dateOfPos) : __('No date value available') !!}</div>
            <div class="product-border">
                {!! !empty($warehouseDet) ? 'Warehouse Name:  ' . e($warehouseDet) : __('No warehouse detail available') !!}
            </div>
        </tbody>
    </table>

    <div class="text-black text-left fs-5 mt-0 mb-0">{{ __('Items') }}</div>

    @if($hasItems)
        @foreach ($items as $value)
            @php
                $name      = data_get($value, 'name', __('No name available'));
                $qty       = data_get($value, 'quantity', __('No quantity available'));
                $price     = data_get($value, 'price', __('No price available'));
                $tax       = data_get($value, 'tax', __('No tax available'));
                $taxAmount = data_get($value, 'tax_amount', __('No tax amount available'));
                $subtotal  = data_get($value, 'subtotal', __('No subtotal available'));
            @endphp
            <div class="mt-2">
                <div class="p-0"><b>{{ $name }}</b></div>
                <div class="d-flex product-border">
                    <div>{{ __('Quantity:') }}</div>
                    <div class="text-end ms-auto">{{ $qty }}</div>
                </div>
            </div>
            <div class="d-flex product-border">
                <div>{{ __('Price:') }}</div>
                <div class="text-end ms-auto">{{ $price }}</div>
            </div>
            <div class="d-flex product-border">
                <div>{{ __('Tax:') }}</div>
                <div class="text-end ms-auto">{{ $tax }}</div>
            </div>
            <div class="d-flex product-border mb-2">
                <div>{{ __('Tax Amount:') }}</div>
                <div class="text-end ms-auto">{{ $taxAmount }}</div>
            </div>
            <div class="d-flex product-border mb-2">
                <div>{{ __('Sub Total:') }}</div>
                <div class="text-end ms-auto">{{ $subtotal }}</div>
            </div>
        @endforeach
    @else
        <div class="mt-2">{{ __('No items to display') }}</div>
    @endif

    <div class="d-flex product-border mb-2 mt-4">
        <div><b>{{ __('Discount:') }}</b></div>
        <div class="text-end ms-auto">{{ $discount }}</div>
    </div>
    <div class="d-flex product-border mb-2">
        <div><b>{{ __('Total:') }}</b></div>
        <div class="text-end ms-auto">{{ $total }}</div>
    </div>

    <h5 class="text-center mt-3 font-label">{{ __('Thank You For Shopping With Us. Please visit again.') }}</h5>
</div>
<div class="justify-content-center pt-2 modal-footer">
    <a href="#" id="print" class="{{ VC::BT_SM_PM }} text-right float-right mb-3">{{ __('Print') }}</a>
    <script async src="{{ asset('assets/js/routes/pos/lang/print.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/pos/print.js') }}"></script>
</div>




