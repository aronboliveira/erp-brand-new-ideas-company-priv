@php
@endphp
@if(!empty($vendor) && ((is_array($vendor ?? null) && count($vendor ?? [])) || (($vendor ?? null) instanceof Collection && $vendor->isNotEmpty())))
    @php
        try {
            $v = ($vendor instanceof Collection) ? $vendor->toArray() : $vendor;

            $billName    = !empty($v['billing_name'])    ? $v['billing_name']    : __('No billing name available');
            $billPhone   = !empty($v['billing_phone'])   ? $v['billing_phone']   : __('No billing phone available');
            $billAddr    = !empty($v['billing_address']) ? $v['billing_address'] : __('No billing address available');
            $billZip     = !empty($v['billing_zip'])     ? $v['billing_zip']     : __('No billing zip available');
            $billCountry = !empty($v['billing_country']) ? $v['billing_country'] : __('No billing country available');
            $billCity    = !empty($v['billing_city'])    ? $v['billing_city']    : __('No billing city available');
            $billState   = !empty($v['billing_state'])   ? $v['billing_state']   : __('No billing state available');

            $shipName    = !empty($v['shipping_name'])    ? $v['shipping_name']    : __('No shipping name available');
            $shipPhone   = !empty($v['shipping_phone'])   ? $v['shipping_phone']   : __('No shipping phone available');
            $shipAddr    = !empty($v['shipping_address']) ? $v['shipping_address'] : __('No shipping address available');
            $shipZip     = !empty($v['shipping_zip'])     ? $v['shipping_zip']     : __('No shipping zip available');
            $shipCountry = !empty($v['shipping_country']) ? $v['shipping_country'] : __('No shipping country available');
            $shipCity    = !empty($v['shipping_city'])    ? $v['shipping_city']    : __('No shipping city available');
            $shipState   = !empty($v['shipping_state'])   ? $v['shipping_state']   : __('No shipping state available');
        } catch (\Throwable $e) {
            \Log::error('bills/vendor_detail — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        }
@endphp
    <div class="row">
        <div class="col-md-5">
            <h6>{{ __('Bill to') }}</h6>
            <div class="bill-to">
                <small>
                    <span>{{ $billName }}</span><br>
                    <span>{{ $billPhone }}</span><br>
                    <span>{{ $billAddr }}</span><br>
                    <span>{{ $billZip }}</span><br>
                    <span>{{ $billCountry }}</span><br>
                    <span>{{ $billCity }}</span><br>
                    <span>{{ $billState }}</span>
                </small>
            </div>
        </div>
        <div class="col-md-5">
            <h6>{{ __('Ship to') }}</h6>
            <div class="bill-to">
                <small>
                    <span>{{ $shipName }}</span><br>
                    <span>{{ $shipPhone }}</span><br>
                    <span>{{ $shipAddr }}</span><br>
                    <span>{{ $shipZip }}</span><br>
                    <span>{{ $shipCountry }}</span><br>
                    <span>{{ $shipState }}</span><br>
                    <span>{{ $shipCity }}</span>
                </small>
            </div>
        </div>
        <div class="{{ VC::CM2 }}">
            <a href="#" id="remove" class="{{ VC::TXSM }}">{{ __(' Remove') }}</a>
        </div>
    </div>
@else
    <div>{{ __('No vendor details available') }}</div>
@endif
