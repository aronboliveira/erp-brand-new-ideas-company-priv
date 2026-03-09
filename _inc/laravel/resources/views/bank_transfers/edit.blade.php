@php
$lang ??= 'en';
	$transferId ??= null;
	$bankTrfUpdateRoute ??= '#';
	$bankTrfFormId ??= 'bank-trf-update-form-unknown';
	$bankTrfUpdateMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$transferId = data_get($transfer ?? null, 'id');
		$bankTrfUpdateRoute = $transferId && Route::has(ViewsConstants::BNK_TRF . '.update')
			? (route(ViewsConstants::BNK_TRF . '.update', $transferId) ?? '#')
			: ($transferId && Route::has(Str::kebab(ViewsConstants::BNK_TRF . '.update'))
				? (route(Str::kebab(ViewsConstants::BNK_TRF . '.update'), $transferId) ?? '#')
				: '#');
		$bankTrfFormId = 'bank-trf-update-form-' . ($transferId ?? 'unknown');
		$bankTrfUpdateMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::BNK_TRF,
			'bank_transfer_update_route_unavailable'
		) ?? 'Bank transfer update route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in bank_transfers/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in bank_transfers/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in bank_transfers/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@php
$fields ??= [];
	$bankAccount ??= [];
	try {
		$fields = [
			['name' => 'from_account', 'type' => 'select', 'label' => __('From Account'), 'options' => $bankAccount, 'colClass' => VC::CM6, 'attrs' => ['class' => VC::FM_CT_SL, 'required' => 'required']],
			['name' => 'to_account', 'type' => 'select', 'label' => __('To Account'), 'options' => $bankAccount, 'colClass' => VC::CM6, 'attrs' => ['class' => VC::FM_CT_SL, 'required' => 'required']],
			['name' => 'amount', 'type' => 'number', 'label' => __('Amount'), 'colClass' => VC::CM6, 'attrs' => ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']],
			['name' => 'date', 'type' => 'date', 'label' => __('Date'), 'colClass' => VC::CM6, 'attrs' => ['class' => VC::FM_CT, 'required' => 'required']],
			['name' => 'reference', 'type' => 'text', 'label' => __('Reference'), 'colClass' => VC::CM6, 'attrs' => ['class' => VC::FM_CT]],
			['name' => 'description', 'type' => 'textarea', 'label' => __('Description'), 'colClass' => VC::C12, 'attrs' => ['class' => VC::FM_CT, 'rows' => 3]],
		];
	} catch (\Error $e) {
		FieldsLog::error('Error in bank_transfers/edit.blade.php fields @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		FieldsLog::error('Exception in bank_transfers/edit.blade.php fields @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		FieldsLog::error('Throwable in bank_transfers/edit.blade.php fields @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($transfer) && isset($transfer?->id))
    {{ Form::model($transfer, [
        'url'              => $bankTrfUpdateRoute,
        'method'           => 'PUT',
        'id'               => $bankTrfFormId,
        'data-url'         => $bankTrfUpdateRoute,
        'data-guard-msg'   => $bankTrfUpdateMsg,
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                @foreach($fields as $f)
                    <div class="{{ VC::FM_G }} {{ $f['colClass'] }}">
                        {{ Form::label($f['name'], $f['label'], ['class' => VC::FM_LB]) }}

                        @if($f['type'] === 'select')
                            {{ Form::select($f['name'], $f['options'], null, $f['attrs']) }}
                        @elseif($f['type'] === 'textarea')
                            {{ Form::textarea($f['name'], null, $f['attrs']) }}
                        @elseif($f['type'] === 'number')
                            {{ Form::number($f['name'], null, $f['attrs']) }}
                        @elseif($f['type'] === 'date')
                            {{ Form::date($f['name'], null, $f['attrs']) }}
                        @elseif($f['type'] === 'text')
                            {{ Form::text($f['name'], null, $f['attrs']) }}
                        @elseif($f['type'] === 'email')
                            {{ Form::email($f['name'], null, $f['attrs']) }}
                        @elseif($f['type'] === 'password')
                            {{ Form::password($f['name'], $f['attrs']) }}
                        @else
                            {{ Form::text($f['name'], null, $f['attrs']) }}
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
            <button type="submit" class="{{ VC::BT_PRM }}">{{ __('Update') }}</button>
        </div>
        <script defer>
            (() => {
                const form = document.getElementById('{{ $bankTrfFormId }}');
                if (!form || form.getAttribute('data-listener-active') === 'true') return;
                form.setAttribute('data-listener-active', 'true');
                form.addEventListener('submit', event => {
                    try {
                        const action = form.getAttribute('action');
                        const url    = form.getAttribute('data-url');
                        if ((action && action !== '#') || (url && url !== '#')) return;
                        event.preventDefault();
                        const msg           = form.getAttribute('data-guard-msg') ?? '# ERROR';
                        (window.RouteGuard?.showToast || (m => alert(m)))(msg);
                        form.setAttribute('data-failed-route', 'true');
                    } catch (e) {}
                });
            })();
        </script>
    {{ Form::close() }}
@else
    <div class="modal-body">
        <div class="{{ VC::ALT_DNG }}">
            {{ __('Transfer data is not available. Please refresh the page and try again.') }}
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Close') }}</button>
    </div>
@endif
