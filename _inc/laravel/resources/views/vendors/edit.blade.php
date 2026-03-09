@php
$lang ??= 'en';
	$basicFields ??= [];
	$billingFields ??= [];
	$shippingFields ??= [];
	$showShipping ??= false;
	$vendorId ??= '';
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateName ??= null;
	$updateUrl ??= '#';
	$updateGuard ??= '';
	$formId ??= 'vendor-update-form';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$basicFields = [
			['name'=>'name','type'=>'text','label'=>__('Name'),'cols'=>6,'required'=>true],
			['name'=>'contact','type'=>'number','label'=>__('Contact'),'cols'=>6,'required'=>true],
			['name'=>'tax_number','type'=>'text','label'=>__('Tax Number'),'cols'=>4],
		];
		$billingFields = [
			['name'=>'billing_name','type'=>'text','label'=>__('Name'),'cols'=>6],
			['name'=>'billing_phone','type'=>'text','label'=>__('Phone'),'cols'=>6],
			['name'=>'billing_address','type'=>'textarea','label'=>__('Address'),'cols'=>12,'rows'=>3],
			['name'=>'billing_city','type'=>'text','label'=>__('City'),'cols'=>6],
			['name'=>'billing_state','type'=>'text','label'=>__('State'),'cols'=>6],
			['name'=>'billing_country','type'=>'text','label'=>__('Country'),'cols'=>6],
			['name'=>'billing_zip','type'=>'text','label'=>__('Zip Code'),'cols'=>6],
		];
		$shippingFields = [
			['name'=>'shipping_name','type'=>'text','label'=>__('Name'),'cols'=>6],
			['name'=>'shipping_phone','type'=>'text','label'=>__('Phone'),'cols'=>6],
			['name'=>'shipping_address','type'=>'textarea','label'=>__('Address'),'cols'=>12,'rows'=>3],
			['name'=>'shipping_city','type'=>'text','label'=>__('City'),'cols'=>6],
			['name'=>'shipping_state','type'=>'text','label'=>__('State'),'cols'=>6],
			['name'=>'shipping_country','type'=>'text','label'=>__('Country'),'cols'=>6],
			['name'=>'shipping_zip','type'=>'text','label'=>__('Zip Code'),'cols'=>6],
		];
		$showShipping = Utility::getValByName('shipping_display') === 'on';
		$vendorId = (string) data_get($vendor ?? null, 'id', '');
		$updateBase = VW::VND . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateName = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateUrl = ($updateName && $vendorId) ? (route($updateName, [$vendorId]) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::VND, 'update_vendor_route_unavailable')
			?? 'Update vendor route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in vendors/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in vendors/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in vendors/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{!! Form::model($vendor, [
    'url'                  => $updateUrl,
    'method'               => 'PUT',
    'id'                   => $formId,
    'data-resolved-action' => $updateUrl,
    'data-guard-msg'       => $updateGuard,
    'data-sv-localized'    => 'true',
]) !!}
    <div class="modal-body">
        <h6 class="sub-title">{{ __('Basic Info') }}</h6>
        <div class="row">
            @foreach($basicFields as $f)
                <div class="{{ VC::CLM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label($f['name'], $f['label'], ['class'=>VC::FM_LB]) }}
                        @php
 $attrs = [ 'class'=>VC::FM_CT ] + (!empty($f['required']) ? ['required'=>'required'] : []);
@endphp
                        {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                    </div>
                </div>
            @endforeach

            @if(!$customFields->isEmpty())
                <div class="{{ VC::CLMS4 }}">
                    <div class="{{ VC::TAB_FD_SH }}" id="tab-2" role="tabpanel">
                        @include(VW::CST_FD . '.formBuilder')
                    </div>
                </div>
            @endif
        </div>

        <h6 class="sub-title">{{ __('Billing Address') }}</h6>
        <div class="row">
            @foreach($billingFields as $f)
                <div class="{{ VC::CLM6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label($f['name'], $f['label'], ['class'=>VC::FM_LB]) }}
                        @php
 $attrs = ['class'=>VC::FM_CT] + (isset($f['rows']) ? ['rows'=>$f['rows']] : []);
@endphp
                        @if($f['type'] === 'textarea')
                            {{ Form::textarea($f['name'], null, $attrs) }}
                        @else
                            {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($showShipping)
            <div class="{{ VC::CM12 }} {{ VC::TX_END }} {{ VC::MB3 }}">
                <button type="button" id="billing_data" class="{{ VC::BT_PRM }}">{{ __('Shipping Same As Billing') }}</button>
            </div>

            <h6 class="sub-title">{{ __('Shipping Address') }}</h6>
            <div class="row">
                @foreach($shippingFields as $f)
                    <div class="{{ VC::CLM6 }}">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label($f['name'], $f['label'], ['class'=>VC::FM_LB]) }}
                            @php
 $attrs = ['class'=>VC::FM_CT] + (isset($f['rows']) ? ['rows'=>$f['rows']] : []);
@endphp
                            @if($f['type'] === 'textarea')
                                {{ Form::textarea($f['name'], null, $attrs) }}
                            @else
                                {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
    </div>

    <script defer src="{{ asset('assets/js/routes/vendors/update.js') }}"></script>
{!! Form::close() !!}
