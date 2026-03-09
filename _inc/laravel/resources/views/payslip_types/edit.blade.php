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
		$hasModel = !empty($paysliptype ?? null) && data_get($paysliptype, 'id');
		$updateBase = VW::PY_SLP_TP . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateUrl = ($updateResolved && $hasModel) ? (route($updateResolved, data_get($paysliptype ?? null, 'id')) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::PY_SLP_TP, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
	} catch (\Error $e) {
		Log::error('Error in payslip_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in payslip_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in payslip_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if($hasModel)
    {{ Form::model($paysliptype, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'paysliptype-update-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                        {{ Form::text('name', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Payslip Type Name')]) }}
                        @error('name')
                            <span class="invalid-name" role="alert">
                                <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/payslips/types/update.js') }}"></script>
    {{ Form::close() }}

@else
    <p>{{ __('The requested payslip type record could not be found or is unavailable.') }}</p>
@endif
