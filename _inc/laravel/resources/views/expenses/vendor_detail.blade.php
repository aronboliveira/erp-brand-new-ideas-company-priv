@php
    try {
        $hasVendor = (is_array($vendor ?? null) && count($vendor ?? []) > 0);

        $b_name    = !empty($vendor['billing_name']   ?? null) ? $vendor['billing_name']   : __('Name not provided');
        $b_phone   = !empty($vendor['billing_phone']  ?? null) ? $vendor['billing_phone']  : __('Phone not provided');
        $b_address = !empty($vendor['billing_address']?? null) ? $vendor['billing_address']: __('Address not provided');
        $b_zip     = !empty($vendor['billing_zip']    ?? null) ? $vendor['billing_zip']    : __('ZIP not provided');
        $b_country = !empty($vendor['billing_country']?? null) ? $vendor['billing_country'] : null;
        $b_city    = !empty($vendor['billing_city']   ?? null) ? $vendor['billing_city']    : null;
        $b_state   = !empty($vendor['billing_state']  ?? null) ? $vendor['billing_state']   : null;
        $b_loc     = ($b_country || $b_city || $b_state)
                     ? implode(' , ', array_filter([$b_country, $b_city, $b_state])) . '.'
                     : __('Location not provided');

        $s_name    = !empty($vendor['shipping_name']   ?? null) ? $vendor['shipping_name']   : __('Name not provided');
        $s_phone   = !empty($vendor['shipping_phone']  ?? null) ? $vendor['shipping_phone']  : __('Phone not provided');
        $s_address = !empty($vendor['shipping_address']?? null) ? $vendor['shipping_address']: __('Address not provided');
        $s_zip     = !empty($vendor['shipping_zip']    ?? null) ? $vendor['shipping_zip']    : __('ZIP not provided');
        $s_country = !empty($vendor['shipping_country']?? null) ? $vendor['shipping_country'] : null;
        $s_state   = !empty($vendor['shipping_state']  ?? null) ? $vendor['shipping_state']   : null;
        $s_city    = !empty($vendor['shipping_city']   ?? null) ? $vendor['shipping_city']    : null;
        $s_loc     = ($s_country || $s_state || $s_city)
                     ? implode(' , ', array_filter([$s_country, $s_state, $s_city])) . '.'
                     : __('Location not provided');
    } catch (\Throwable $e) {
        \Log::error('expenses/vendor_detail — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    }
@endphp

@if($hasVendor)
    <div class="row">
        <div class="col-md-5">
            <h6>{{ __('Bill to') }}</h6>
            <div class="bill-to">
                <small>
                    <span>{{ $b_name }}</span><br>
                    <span>{{ $b_phone }}</span><br>
                    <span>{{ $b_address }}</span><br>
                    <span>{{ $b_zip }}</span><br>
                    <span>{{ $b_loc }}</span>
                </small>
            </div>
        </div>

        <div class="col-md-5">
            <h6>{{ __('Ship to') }}</h6>
            <div class="bill-to">
                <small>
                    <span>{{ $s_name }}</span><br>
                    <span>{{ $s_phone }}</span><br>
                    <span>{{ $s_address }}</span><br>
                    <span>{{ $s_zip }}</span><br>
                    <span>{{ $s_loc }}</span>
                </small>
            </div>
        </div>

        <div class="{{ VC::CM2 }}">
            <a href="#" id="remove" class="{{ VC::TXSM }}">{{ __(' Remove') }}</a>
        </div>
    </div>
@else
    <div class="row">
        <div class="{{ VC::C12 }} {{ VC::TXCT_MT }}">{{ __('Vendor details not available.') }}</div>
    </div>
@endif
