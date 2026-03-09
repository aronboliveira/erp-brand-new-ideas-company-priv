@php
$lang ??= 'en';
	$terminationTypeId ??= null;
	$updateBaseName ??= '';
	$updateKebabName ??= '';
	$updateResolvedName ??= null;
	$updateUrl ??= '#';
	$formId ??= 'termination-type-update-form';
	$guardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$terminationTypeId = data_get($terminationtype ?? null, 'id', null);
		$updateBaseName = ViewsConstants::TMN_TP . '.update';
		$updateKebabName = Str::kebab($updateBaseName);
		$updateResolvedName = Route::has($updateBaseName)
			? $updateBaseName
			: (Route::has($updateKebabName) ? $updateKebabName : null);
		$updateUrl = ($updateResolvedName && $terminationTypeId)
			? (route($updateResolvedName, [$terminationTypeId]) ?? '#')
			: '#';
		$guardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::TMN_TP, 'update_termination_type_route_unavailable')
			?? 'Update termination type route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in termination_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in termination_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in termination_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::model($terminationtype, [
    'url'    => $updateUrl,
    'method' => 'PUT',
    'id'     => $formId,
    'data-url' => $updateUrl,
    'data-guard-msg' => $guardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::CM12 }}">
                <div class="{{ VC::FM_G }}">
                    {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                    {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Termination Type Name')]) }}
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
    <script src="{{ asset('assets/js/routes/terminations/types/update.js') }}" defer></script>
{{ Form::close() }}
