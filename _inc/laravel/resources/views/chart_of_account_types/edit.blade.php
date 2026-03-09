@php
$lang ??= 'en';
	$routeName ??= '';
	$updateRoute ??= '#';
	$formId ??= 'chartOfAccountTypeUpdateForm_unknown';
	$guardMsg ??= '';
	$chartOfAccountTypeId ??= null;
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$chartOfAccountTypeId = data_get($chartOfAccountType ?? null, 'id');
		$routeName = ViewsConstants::COA_TP . '.update';
		$updateRoute = ($chartOfAccountTypeId && Route::has($routeName))
			? (route($routeName, $chartOfAccountTypeId) ?? '#')
			: (($chartOfAccountTypeId && Route::has(Str::kebab($routeName)))
				? (route(Str::kebab($routeName), $chartOfAccountTypeId) ?? '#')
				: '#');
		$formId = 'chartOfAccountTypeUpdateForm_' . ($chartOfAccountTypeId ?? 'unknown');
		$guardMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::COA_TP,
			'chart_of_account_type_update_route_unavailable'
		) ?? 'Chart of Account Type update route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in chart_of_account_types/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in chart_of_account_types/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in chart_of_account_types/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

<div class="{{ VC::CD }} bg-none card-box">
    {{ Form::model($chartOfAccountType, [
        'url'            => $updateRoute,
        'method'         => 'PUT',
        'id'             => $formId,
        'data-url'       => $updateRoute,
        'data-guard-msg' => $guardMsg,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                @error('name')
                    <small class="invalid-name" role="alert">
                        <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                    </small>
                @enderror
            </div>
        </div>
        <div class="modal-footer">
            <input
                type="button"
                value="{{ __('Cancel') }}"
                class="{{ VC::BT_LG }}"
                data-bs-dismiss="modal"
            >
            <input
                type="submit"
                value="{{ __('Update') }}"
                class="{{ VC::BT_PRM }}"
            >
        </div>
    {{ Form::close() }}
</div>
