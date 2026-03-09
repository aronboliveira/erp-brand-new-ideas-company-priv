@php
$lang ??= 'en';
	$hasModel ??= false;
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateResolved ??= null;
	$updateUrl ??= '#';
	$updateGuard ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$hasModel = !empty($payment ?? null) && data_get($payment, 'id');
		$updateBase = 'payment.update';
		$updateKebab = Str::kebab($updateBase);
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateUrl = ($updateResolved && $hasModel) ? (route($updateResolved, $payment->id) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, 'payment', 'update_route_unavailable')
			?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
	} catch (\Error $e) {
		Log::error('Error in payments/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in payments/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in payments/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if($hasModel)
    {{ Form::model($payment, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'enctype'           => 'multipart/form-data',
        'id'                => 'payment-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::FM_GCB6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('vendor_id', __('Vendor'), ['class' => VC::FM_LB]) }}
                        {{ Form::select('vendor_id', $vendors, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                    </div>
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('date', __('Date'), ['class' => VC::FM_LB]) }}
                        {{ Form::date('date', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                    </div>
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('amount', __('Amount'), ['class' => VC::FM_LB]) }}
                        {{ Form::number('amount', null, ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
                    </div>
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('category_id', __('Category'), ['class' => VC::FM_LB]) }}
                        {{ Form::select('category_id', $categories, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                    </div>
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('account_id', __('Account'), ['class' => VC::FM_LB]) }}
                        {{ Form::select('account_id', $accounts, null, ['class' => VC::FM_CT_SL, 'required' => 'required']) }}
                    </div>
                </div>
                <div class="{{ VC::FM_GCB6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('reference', __('Reference'), ['class' => VC::FM_LB]) }}
                        {{ Form::text('reference', null, ['class' => VC::FM_CT]) }}
                    </div>
                </div>

                <div class="{{ VC::FM_GCB6 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('add_receipt', __('Payment Receipt'), ['class' => VC::FM_LB]) }}
                        {{ Form::file('add_receipt', ['class' => VC::FM_CT, 'id' => 'payment-files']) }}
                        @php
 $existing = $payment->add_receipt ?? null;
@endphp
                        @if(!empty($existing))
                            <img id="payment-image"
                                class="{{ VC::MT2 }}"
                                src="{{ asset(Storage::url('uploads/payment')).'/'.$existing }}"
                                style="width:25%;"
                                alt="{{ __('Existing receipt preview') }}">
                        @else
                            <small class="{{ VC::TXT_MT }}">{{ __('No receipt uploaded.') }}</small>
                            <img id="payment-image"
                                class="{{ VC::MT2 }}"
                                style="width:25%; display:none;"
                                alt="{{ __('Receipt preview') }}">
                        @endif
                    </div>
                </div>

                <div class="{{ VC::FM_GCB12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('description', __('Description'), ['class' => VC::FM_LB]) }}
                        {{ Form::textarea('description', null, ['class' => VC::FM_CT, 'rows' => 3]) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/payments/update.js') }}"></script>
    {{ Form::close() }}
@else
    <p>{{ __('The requested payment record could not be found or is unavailable.') }}</p>
@endif
