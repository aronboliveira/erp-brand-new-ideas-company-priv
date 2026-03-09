@php
$lang ??= 'en';
	$routeName ??= '';
	$updateRoute ??= '#';
	$formId ??= 'custom-field-update-form-unknown';
	$guardMsg ??= '';
	$customFieldId ??= null;
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$customFieldId = data_get($customField ?? null, 'id');
		$routeName = ViewsConstants::CST_FD . '.update';
		$updateRoute = ($customFieldId && Route::has($routeName))
			? (route($routeName, $customFieldId) ?? '#')
			: (($customFieldId && Route::has(Str::kebab($routeName)))
				? (route(Str::kebab($routeName), $customFieldId) ?? '#')
				: '#');
		$formId = 'custom-field-update-form-' . ($customFieldId ?? 'unknown');
		$guardMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::CST_FD,
			'custom_field_update_route_unavailable'
		) ?? 'Custom Field update route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in custom_fields/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in custom_fields/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in custom_fields/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($customField) && isset($customField->id))
    {{ Form::model($customField, [
        'url'            => $updateRoute,
        'method'         => 'PUT',
        'id'             => $formId,
        'data-url'       => $updateRoute,
        'data-guard-msg' => $guardMsg,
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                    {{ Form::label('name', __('Custom Field Name'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
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
        <script defer>window.RouteGuard?.guardFormSubmit?.('{{ $formId }}');</script>
    {{ Form::close() }}
@else
    <div class="{{ VC::ALT_DNG }}">
        {{ __('Invalid Custom Field data.') }}
    </div>
@endif
