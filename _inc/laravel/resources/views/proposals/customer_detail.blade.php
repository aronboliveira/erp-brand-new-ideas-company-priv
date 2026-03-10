@if(isset($customer) && (is_array($customer) || is_object($customer)) && !empty($customer))
    @php
        $isArray = is_array($customer);
    @endphp
    <div class="row">
        <div class="col-md-5">
            <h6>{{__('Bill to')}}</h6>
            <div class="bill-to">
                @php
                    $billingName = $isArray ? ($customer['billing_name'] ?? __('No billing name provided')) : ($customer->billing_name ?? __('No billing name provided'));
                @endphp
                @if(!empty($billingName) && $billingName !== __('No billing name provided'))
                    <small>
                        <span>{{ $billingName }}</span><br>
                        @php
                            $billingPhone = $isArray ? ($customer['billing_phone'] ?? __('No phone number')) : ($customer->billing_phone ?? __('No phone number'));
                        @endphp
                        @if(!empty($billingPhone) && $billingPhone !== __('No phone number'))
                            <span>{{ $billingPhone }}</span><br>
                        @endif
                        @php
                            $billingAddress = $isArray ? ($customer['billing_address'] ?? __('No street address')) : ($customer->billing_address ?? __('No street address'));
                        @endphp
                        @if(!empty($billingAddress) && $billingAddress !== __('No street address'))
                            <span>{{ $billingAddress }}</span><br>
                        @endif
                        @php
                            $billingCity = $isArray ? ($customer['billing_city'] ?? __('City not specified')) : ($customer->billing_city ?? __('City not specified'));
                            $billingState = $isArray ? ($customer['billing_state'] ?? __('State not specified')) : ($customer->billing_state ?? __('State not specified'));
                            $billingCountry = $isArray ? ($customer['billing_country'] ?? __('Country not specified')) : ($customer->billing_country ?? __('Country not specified'));
                            $locationParts = array_filter([$billingCity, $billingState, $billingCountry], function($value) {
                                return !empty($value) && 
                                       $value !== __('City not specified') && 
                                       $value !== __('State not specified') && 
                                       $value !== __('Country not specified');
                            });
                        @endphp
                        @if(!empty($locationParts))
                            <span>{{ implode(' , ', $locationParts) }}.</span><br>
                        @endif
                        @php
                            $billingZip = $isArray ? ($customer['billing_zip'] ?? __('No postal code')) : ($customer->billing_zip ?? __('No postal code'));
                        @endphp
                        @if(!empty($billingZip) && $billingZip !== __('No postal code'))
                            <span>{{ $billingZip }}</span>
                        @endif
                    </small>
                @else
                    <small class="text-muted">{{ __('No billing information available') }}</small>
                @endif
            </div>
        </div>
        <div class="col-md-5">
            <h6>{{__('Ship to')}}</h6>
            <div class="bill-to">
                @php
                    $shippingName = $isArray ? ($customer['shipping_name'] ?? __('No recipient name')) : ($customer->shipping_name ?? __('No recipient name'));
                @endphp
                @if(!empty($shippingName) && $shippingName !== __('No recipient name'))
                    <small>
                        <span>{{ $shippingName }}</span><br>
                        @php
                            $shippingPhone = $isArray ? ($customer['shipping_phone'] ?? __('No contact number')) : ($customer->shipping_phone ?? __('No contact number'));
                        @endphp
                        @if(!empty($shippingPhone) && $shippingPhone !== __('No contact number'))
                            <span>{{ $shippingPhone }}</span><br>
                        @endif
                        @php
                            $shippingAddress = $isArray ? ($customer['shipping_address'] ?? __('No delivery address')) : ($customer->shipping_address ?? __('No delivery address'));
                        @endphp
                        @if(!empty($shippingAddress) && $shippingAddress !== __('No delivery address'))
                            <span>{{ $shippingAddress }}</span><br>
                        @endif
                        @php
                            $shippingCity = $isArray ? ($customer['shipping_city'] ?? __('Delivery city not specified')) : ($customer->shipping_city ?? __('Delivery city not specified'));
                            $shippingState = $isArray ? ($customer['shipping_state'] ?? __('Delivery state not specified')) : ($customer->shipping_state ?? __('Delivery state not specified'));
                            $shippingCountry = $isArray ? ($customer['shipping_country'] ?? __('Delivery country not specified')) : ($customer->shipping_country ?? __('Delivery country not specified'));
                            $shippingParts = array_filter([$shippingCity, $shippingState, $shippingCountry], function($value) {
                                return !empty($value) && 
                                       $value !== __('Delivery city not specified') && 
                                       $value !== __('Delivery state not specified') && 
                                       $value !== __('Delivery country not specified');
                            });
                        @endphp
                        @if(!empty($shippingParts))
                            <span>{{ implode(' , ', $shippingParts) }}.</span><br>
                        @endif
                        @php
                            $shippingZip = $isArray ? ($customer['shipping_zip'] ?? __('No delivery postal code')) : ($customer->shipping_zip ?? __('No delivery postal code'));
                        @endphp
                        @if(!empty($shippingZip) && $shippingZip !== __('No delivery postal code'))
                            <span>{{ $shippingZip }}</span>
                        @endif
                    </small>
                @else
                    <small class="text-muted">{{ __('No shipping information available') }}</small>
                @endif
            </div>
        </div>
        <div class="col-md-2">
            <a href="#" id="remove" class="text-sm">{{__(' Remove')}}</a>
        </div>
    </div>
@else
    <div class="row">
        <div class="col-md-12">
            <small class="text-muted">{{ __('Customer information is not available') }}</small>
        </div>
    </div>
@endif