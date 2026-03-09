@php
$lang ??= 'en';
	$hasEntity ??= false;
	$formId ??= 'fm-bd-update-form';
	$updBase ??= '';
	$updKebab ??= '';
	$updRes ??= null;
	$updUrl ??= '#';
	$updGuard ??= '';
	$activeVal ??= 1;
	$formBuilderId ??= null;
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$formBuilderId = data_get($formBuilder ?? null, 'id');
		$hasEntity = !empty($formBuilder ?? null) && $formBuilderId;
		if ($hasEntity) {
			$updBase = VW::FM_BD . '.update';
			$updKebab = Str::kebab($updBase);
			$updRes = Route::has($updBase) ? $updBase : (Route::has($updKebab) ? $updKebab : null);
			$updUrl = ($updRes && $formBuilderId) ? (route($updRes, $formBuilderId) ?? '#') : '#';
			$updGuard = Utility::fetchLinkMessage($lang, VW::FM_BD, 'update_route_unavailable') ?? __('Form update route is unavailable. Please contact technical support or your domain administrator.');
			$activeVal = (int) data_get($formBuilder ?? null, 'is_active', 1);
		}
	} catch (\Error $e) {
		Log::error('Error in form_builders/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in form_builders/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in form_builders/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if(!$hasEntity)
    <div class="{{ VC::ALT_DNG }} {{ VC::MB0 }}" role="alert">{{ __('The requested form builder was not found or is unavailable.') }}</div>
@else
    {{ Form::model($formBuilder, [
        'url'               => $updUrl,
        'method'            => 'PUT',
        'id'                => $formId,
        'data-url'          => $updUrl,
        'data-guard-msg'    => $updGuard,
        'data-sv-localized' => 'true',
    ]) }}
    <div class="modal-body">
        <div class="row">
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                {{ Form::label('name', __('Name') ?: __('Failed to get label: Name'), ['class'=>'form-label']) }}
                {{ Form::text('name', null, ['class'=>'form-control','required'=>'required','placeholder'=>__('Enter name') ?: __('Failed to get placeholder: name')]) }}
            </div>
            <div class="{{ VC::C12 }} {{ VC::FM_G }}">
                <label class="{{ VC::FM_LB }}">{{ __('Active') ?: __('Failed to get label: Active') }}</label>
                <div class="{{ VC::DFL }} radio-check">
                    <div class="{{ VC::FM_CHK_IL }}">
                        <input type="radio" id="on" value="1" name="is_active" class="form-check-input" {{ $activeVal === 1 ? 'checked' : '' }}>
                        <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="on">{{ __('On') ?: __('Failed to get label: On') }}</label>
                    </div>
                    <div class="{{ VC::FM_CHK_IL }}">
                        <input type="radio" id="off" value="0" name="is_active" class="form-check-input" {{ $activeVal === 0 ? 'checked' : '' }}>
                        <label class="{{ VC::CST_LB }} {{ VC::FM_LB }}" for="off">{{ __('Off') ?: __('Failed to get label: Off') }}</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') ?: __('Failed to get label: Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Update') ?: __('Failed to get label: Update') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/formBuilders/update.js') }}"></script>
    {{ Form::close() }}

@endif
