@php
$lang ??= 'en';
	$contractTypeUpdateRoute ??= '#';
	$contractTypeFormId ??= 'contract-type-update-form';
	$contractTypeUpdateMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$contractTypeId = data_get($contractType ?? null, 'id');
		$contractTypeUpdateRoute = $contractTypeId && Route::has(ViewsConstants::CTC_TP . '.update')
			? (route(ViewsConstants::CTC_TP . '.update', $contractTypeId) ?? '#')
			: ($contractTypeId && Route::has(Str::kebab(ViewsConstants::CTC_TP . '.update'))
				? (route(Str::kebab(ViewsConstants::CTC_TP . '.update'), $contractTypeId) ?? '#')
				: '#');
		$contractTypeFormId = 'contract-type-update-form-' . ($contractTypeId ?: 'unknown');
		$contractTypeUpdateMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::CTC_TP,
			'contract_type_update_route_unavailable'
		) ?? 'Contract Type update route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in contract_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in contract_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in contract_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if(!empty($contractType) && isset($contractType->id))
    {{ Form::model($contractType, [
        'url'            => $contractTypeUpdateRoute,
        'method'         => 'PUT',
        'id'             => $contractTypeFormId,
        'data-url'       => $contractTypeUpdateRoute,
        'data-guard-msg' => $contractTypeUpdateMsg,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
    {{ Form::close() }}
@else
    <div class="alert {{ VC::TXT_MT }}">{{ __('Contract Type not found') }}</div>
@endif
