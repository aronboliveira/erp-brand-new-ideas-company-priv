@php
    use Illuminate\Support\Collection;

    $isArray      = is_array($customer ?? null) && count($customer ?? []) > 0;
    $isCollection = ($customer ?? null) instanceof Collection && ($customer)->isNotEmpty();

    $bn  = !empty($customer['billing_name'])    ? $customer['billing_name']    : __('Name not provided');
    $bp  = !empty($customer['billing_phone'])   ? $customer['billing_phone']   : __('Phone not provided');
    $ba  = !empty($customer['billing_address']) ? $customer['billing_address'] : __('Address not provided');
    $bc  = !empty($customer['billing_city'])    ? $customer['billing_city']    : __('City not specified');
    $bs  = !empty($customer['billing_state'])   ? $customer['billing_state']   : __('State not specified');
    $bco = !empty($customer['billing_country']) ? $customer['billing_country'] : __('Country not specified');
    $bz  = !empty($customer['billing_zip'])     ? $customer['billing_zip']     : __('ZIP not specified');

    $sn  = !empty($customer['shipping_name'])    ? $customer['shipping_name']    : __('Name not provided');
    $sp  = !empty($customer['shipping_phone'])   ? $customer['shipping_phone']   : __('Phone not provided');
    $sa  = !empty($customer['shipping_address']) ? $customer['shipping_address'] : __('Address not provided');
    $sc  = !empty($customer['shipping_city'])    ? $customer['shipping_city']    : __('City not specified');
    $ss  = !empty($customer['shipping_state'])   ? $customer['shipping_state']   : __('State not specified');
    $sco = !empty($customer['shipping_country']) ? $customer['shipping_country'] : __('Country not specified');
    $sz  = !empty($customer['shipping_zip'])     ? $customer['shipping_zip']     : __('ZIP not specified');
@endphp

@if($isArray || $isCollection)
    <div class="row">
        <div class="col-md-5">
            <h6>{{ __('Bill to') }}</h6>
            <div class="bill-to">
                <small>
                    <span>{{ $bn }}</span><br>
                    <span>{{ $bp }}</span><br>
                    <span>{{ $ba }}</span><br>
                    <span>{{ $bc . ' , ' . $bs . ' , ' . $bco . '.' }}</span><br>
                    <span>{{ $bz }}</span>
                </small>
            </div>
        </div>

        <div class="col-md-5">
            <h6>{{ __('Ship to') }}</h6>
            <div class="bill-to">
                <small>
                    <span>{{ $sn }}</span><br>
                    <span>{{ $sp }}</span><br>
                    <span>{{ $sa }}</span><br>
                    <span>{{ $sc . ' , ' . $ss . ' , ' . $sco . '.' }}</span><br>
                    <span>{{ $sz }}</span>
                </small>
            </div>
        </div>

        <div class="col-md-2">
            <a href="#" id="remove" class="text-sm">{{ __(' Remove') }}</a>
        </div>
    </div>
@else
    <div class="row">
        <div class="col-12 text-muted text-center">{{ __('Customer details not available.') }}</div>
    </div>
@endif
