@php
$lang ??= 'en';
	$hasGoalType ??= false;
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateResolved ??= null;
	$updateUrl ??= '#';
	$updateGuardMsg ??= '';
	$goalTypeId ??= null;
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$goalTypeId = data_get($goalType ?? null, 'id');
		$hasGoalType = !empty($goalType ?? null) && $goalTypeId;
		$updateBase = VW::GL_TP . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateUrl = ($updateResolved && $goalTypeId) ? (route($updateResolved, $goalTypeId) ?? '#') : '#';
		$updateGuardMsg = Utility::fetchLinkMessage($lang, VW::GL_TP, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
	} catch (\\Error $e) {
		Log::error('Error in goal_types/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\\Exception $e) {
		Log::error('Exception in goal_types/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\\Throwable $e) {
		Log::error('Throwable in goal_types/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if(!$hasGoalType)
    <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ __('The requested goal type was not found or is unavailable.') }}</div>
@else
    {{ Form::model($goalType, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'goal-type-edit-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuardMsg,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::CM12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
                        {{ Form::text('name', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter Goal Type Name')]) }}
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

        <script defer src="{{ asset('assets/js/routes/goals/types/edit.js') }}"></script>
    {{ Form::close() }}
@endif
