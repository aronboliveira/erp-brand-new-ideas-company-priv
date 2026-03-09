@php
$lang ??= 'en';
	$hasLT ??= false;
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateResolved ??= null;
	$updateUrl ??= '#';
	$updateGuard ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$hasLT = !empty($leavetype ?? null) && data_get($leavetype, 'id');
		$updateBase = VW::LV_TP . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$leaveTypeId = data_get($leavetype ?? null, 'id', '');
		$updateUrl = ($updateResolved && $hasLT && $leaveTypeId) ? (route($updateResolved, $leaveTypeId) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::LV_TP, 'update_route_unavailable')
			?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
	} catch (\Error $e) {
		Log::error('Error in leave_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in leave_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in leave_types/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if($hasLT)
    {{ Form::model($leavetype, [
        'url'            => $updateUrl,
        'method'         => 'PUT',
        'id'             => 'leaveType-edit-form',
        'data-guard-msg' => $updateGuard
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('title', __('Leave Type'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('title', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Leave Type Name')]) }}
                    @error('title')
                        <span class="invalid-name" role="alert">
                            <strong class="{{ VC::TX_DNG }}">{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('days', __('Days Per Year'), ['class' => VC::FM_LB]) }}
                    {{ Form::number('days', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Days / Year'), 'min' => 0]) }}
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/leaves/types/update.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="{{ VC::TXS }} {{ VC::TXT_MT }}">{{ __('Requested leave type was not found or is unavailable.') }}</div>
@endif
