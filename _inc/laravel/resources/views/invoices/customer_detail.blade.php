@if((is_array($customer ?? null) && count($customer) > 0) || ($customer instanceof \Illuminate\Support\Collection && $customer->isNotEmpty()) || is_object($customer ?? null))
	<div class="row">
		<div class="col-md-5">
			<h6>{{ __('Bill to') }}</h6>
			<div class="bill-to">
				<small>
					<span>{{ isset($customer['billing_name']) && $customer['billing_name'] !== '' ? $customer['billing_name'] : __('No billing name available') }}</span><br>
					<span>{{ isset($customer['billing_phone']) && $customer['billing_phone'] !== '' ? $customer['billing_phone'] : __('No billing phone available') }}</span><br>
					<span>{{ isset($customer['billing_address']) && $customer['billing_address'] !== '' ? $customer['billing_address'] : __('No billing address available') }}</span><br>
					<span>{{ (isset($customer['billing_city']) && $customer['billing_city'] !== '' ? $customer['billing_city'] : __('No city available')) . ' , ' . (isset($customer['billing_state']) && $customer['billing_state'] !== '' ? $customer['billing_state'] : __('No state available')) . ' , ' . (isset($customer['billing_country']) && $customer['billing_country'] !== '' ? $customer['billing_country'] : __('No country available')) . '.' }}</span><br>
					<span>{{ isset($customer['billing_zip']) && $customer['billing_zip'] !== '' ? $customer['billing_zip'] : __('No billing ZIP available') }}</span>
				</small>
			</div>
		</div>
		<div class="col-md-5">
			<h6>{{ __('Ship to') }}</h6>
			<div class="bill-to">
				<small>
					<span>{{ isset($customer['shipping_name']) && $customer['shipping_name'] !== '' ? $customer['shipping_name'] : __('No shipping name available') }}</span><br>
					<span>{{ isset($customer['shipping_phone']) && $customer['shipping_phone'] !== '' ? $customer['shipping_phone'] : __('No shipping phone available') }}</span><br>
					<span>{{ isset($customer['shipping_address']) && $customer['shipping_address'] !== '' ? $customer['shipping_address'] : __('No shipping address available') }}</span><br>
					<span>{{ (isset($customer['shipping_city']) && $customer['shipping_city'] !== '' ? $customer['shipping_city'] : __('No city available')) . ' , ' . (isset($customer['shipping_state']) && $customer['shipping_state'] !== '' ? $customer['shipping_state'] : __('No state available')) . ' , ' . (isset($customer['shipping_country']) && $customer['shipping_country'] !== '' ? $customer['shipping_country'] : __('No country available')) . '.' }}</span><br>
					<span>{{ isset($customer['shipping_zip']) && $customer['shipping_zip'] !== '' ? $customer['shipping_zip'] : __('No shipping ZIP available') }}</span>
				</small>
			</div>
		</div>
		<div class="{{ VC::CM2 }}">
			<a href="#" id="remove" class="{{ VC::TXSM }}">{{ __(' Remove') }}</a>
		</div>
	</div>
@else
	<div class="row"><div class="{{ VC::C12 }} {{ VC::TXCT }}">{{ __('No customer data available') }}</div></div>
@endif
