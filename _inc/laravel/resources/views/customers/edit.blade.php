@php
$basicFields ??= [];
	$billingFields ??= [];
	$shippingFields ??= [];
	$customersUpdateBaseRouteName ??= '';
	$customersUpdateKebabRouteName ??= '';
	$customerIdValue ??= '';
	$customersUpdateResolvedName ??= null;
	$customersUpdateUrl ??= '#';
	$customersUpdateFormId ??= 'customers-update-form-x';
	$userLang ??= 'en';
	$customersUpdateGuardMessage ??= '';
	try {
		$basicFields = [
			['name'=>'name','type'=>'text','label'=>__('Name'),'cols'=>4,'attrs'=>['required'=>'required']],
			['name'=>'contact','type'=>'number','label'=>__('Contact'),'cols'=>4,'attrs'=>['required'=>'required']],
			['name'=>'email','type'=>'text','label'=>__('Email'),'cols'=>4],
			['name'=>'tax_number','type'=>'text','label'=>__('Tax Number'),'cols'=>4],
		];
		$billingFields = [
			['name'=>'billing_name','type'=>'text','label'=>__('Name'),'cols'=>6],
			['name'=>'billing_phone','type'=>'text','label'=>__('Phone'),'cols'=>6],
			['name'=>'billing_address','type'=>'textarea','label'=>__('Address'),'cols'=>12,'attrs'=>['rows'=>3]],
			['name'=>'billing_city','type'=>'text','label'=>__('City'),'cols'=>6],
			['name'=>'billing_state','type'=>'text','label'=>__('State'),'cols'=>6],
			['name'=>'billing_country','type'=>'text','label'=>__('Country'),'cols'=>6],
			['name'=>'billing_zip','type'=>'text','label'=>__('Zip Code'),'cols'=>6],
		];
		$shippingFields = [
			['name'=>'shipping_name','type'=>'text','label'=>__('Name'),'cols'=>6],
			['name'=>'shipping_phone','type'=>'text','label'=>__('Phone'),'cols'=>6],
			['name'=>'shipping_address','type'=>'textarea','label'=>__('Address'),'cols'=>12,'attrs'=>['rows'=>3]],
			['name'=>'shipping_city','type'=>'text','label'=>__('City'),'cols'=>6],
			['name'=>'shipping_state','type'=>'text','label'=>__('State'),'cols'=>6],
			['name'=>'shipping_country','type'=>'text','label'=>__('Country'),'cols'=>6],
			['name'=>'shipping_zip','type'=>'text','label'=>__('Zip Code'),'cols'=>6],
		];
		$customersUpdateBaseRouteName = ViewsConstants::CST . '.update';
		$customersUpdateKebabRouteName = Str::kebab($customersUpdateBaseRouteName);
		$customerIdValue = (string) data_get($customer ?? null, 'id', '');
		$customersUpdateResolvedName = Route::has($customersUpdateBaseRouteName)
			? $customersUpdateBaseRouteName
			: (Route::has($customersUpdateKebabRouteName) ? $customersUpdateKebabRouteName : null);
		$customersUpdateUrl = ($customersUpdateResolvedName && $customerIdValue !== '')
			? (route($customersUpdateResolvedName, $customerIdValue) ?? '#')
			: '#';
		$customersUpdateFormId = 'customers-update-form-' . ($customerIdValue === '' ? 'x' : $customerIdValue);
		$userLang = Utility::fetchUserLang() ?? 'en';
		$customersUpdateGuardMessage = Utility::fetchLinkMessage($userLang, ViewsConstants::CST, 'update_customer_route_unavailable')
			?? 'Update customer route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in customers/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in customers/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in customers/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(empty($customer) || !isset($customer->id))
    <div class="{{ VC::ALT_DNG }}">
        {{ __('Customer data is not available. Please contact technical support or your domain administrator.') }}
    </div>
@else
    {{ Form::model($customer, [
        'method'            => 'PUT',
        'url'               => $customersUpdateUrl,
        'id'                => $customersUpdateFormId,
        'data-url'          => $customersUpdateUrl,
        'data-guard-msg'    => $customersUpdateGuardMessage,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <h6 class="sub-title">{{ __('Basic Info') }}</h6>
            <div class="{{ VC::RW }}">
                @foreach($basicFields as $f)
                    <div class="{{ VC::CLMS4 }}">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}
                            @php
 $attrs = array_merge(['class' => VC::FM_CT], $f['attrs'] ?? [])
@endphp
                            @if($f['type'] === 'textarea')
                                {{ Form::textarea($f['name'], null, $attrs) }}
                            @else
                                {{ Form::{$f['type']}($f['name'], null, $attrs) }}
                            @endif
                        </div>
                    </div>
                @endforeach
                @if(!$customFields->isEmpty())
                    <div class="{{ VC::CLMS4 }}">
                        <div class="{{ VC::TAB_FD_SH }}" id="tab-2" role="tabpanel">
                            @include(ViewsConstants::CST_FD . '.formBuilder')
                        </div>
                    </div>
                @endif
            </div>

            <h6 class="sub-title">{{ __('Billing Address') }}</h6>
            <div class="{{ VC::RW }}">
                @foreach($billingFields as $f)
                    <div class="{{ VC::CLM6 }}">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}
                            @php
 $attrs = array_merge(['class' => VC::FM_CT], $f['attrs'] ?? [])
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

            @if(\App\Models\Utility::getValByName('shipping_display') === 'on')
                <div class="{{ VC::C12 }} text-end">
                    <button type="button" id="billing_data" class="{{ VC::BT_PRM }}">{{ __('Shipping Same As Billing') }}</button>
                </div>

                <h6 class="sub-title">{{ __('Shipping Address') }}</h6>
                <div class="{{ VC::RW }}">
                    @foreach($shippingFields as $f)
                        <div class="{{ VC::CLM6 }}">
                            <div class="{{ VC::FM_G }}">
                                {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}
                                @php
 $attrs = array_merge(['class' => VC::FM_CT], $f['attrs'] ?? [])
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
        <script defer>
            (() => {
                try {
                    const formEl = document.getElementById('{{ $customersUpdateFormId }}');
                    if (!formEl) { return; }
                    if (formEl.getAttribute('data-listener-active') === 'true') { return; }
                    formEl.setAttribute('data-listener-active','true');

                    formEl.addEventListener('submit', (e) => {
                        try {
                            const action = formEl.getAttribute('action') ?? '#';
                            const url    = formEl.getAttribute('data-url') ?? action ?? '#';
                            if (url !== '#' && action !== '#') { return; }
                            e.preventDefault();

                            const msg = formEl.getAttribute('data-guard-msg') ?? 'Update customer route is unavailable. Please contact technical support or your domain administrator.';
                            (window.RouteGuard?.showToast || (m => alert(m)))(msg);

                            formEl.setAttribute('data-failed-route','true');
                        } catch (err) {}
                    });
                } catch (err) {}
            })();
        </script>
    {{ Form::close() }}
@endif
