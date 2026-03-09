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
		$hasModel = !empty($otherpayment ?? null) && data_get($otherpayment, 'id');
		$updateBase = VW::OT_PAY . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateUrl = ($updateResolved && $hasModel) ? (route($updateResolved, data_get($otherpayment ?? null, 'id')) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::OT_PAY, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
	} catch (\\Error $e) {
		Log::error('Error in other_payments/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\\Exception $e) {
		Log::error('Exception in other_payments/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\\Throwable $e) {
		Log::error('Throwable in other_payments/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if($hasModel)
    {{ Form::model($otherpayment, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'otherpayment-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::CD_BD }} p-0">
                <div class="row">
                    <div class="{{ VC::FM_GCB12 }}">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label('title', __('Title'), ['class' => 'form-label']) }}
                            {{ Form::text('title', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="{{ VC::FM_GCB6 }}">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label('type', __('Type'), ['class' => 'form-label']) }}
                            {{ Form::select('type', $otherpaytypes, null, ['class' => VC::FM_CT_SL . ' amount_type', 'required' => 'required']) }}
                        </div>
                    </div>
                    <div class="{{ VC::FM_GCB6 }}">
                        <div class="{{ VC::FM_G }}">
                            {{ Form::label('amount', __('Amount'), ['class' => 'form-label amount_label']) }}
                            {{ Form::number('amount', null, ['class' => VC::FM_CT, 'required' => 'required', 'step' => '0.01']) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/otherPayments/update.js') }}"></script>
    {{ Form::close() }}
@else
    <p>{{ __('The requested payment could not be found or is unavailable.') }}</p>
@endif
