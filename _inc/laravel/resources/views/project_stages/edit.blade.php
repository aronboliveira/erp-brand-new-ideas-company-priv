@php
$lang ??= 'en';
	$leadstageId ??= null;
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateResolved ??= null;
	$updateParams ??= ['#'];
	$updateUrl ??= '#';
	$formId ??= 'update-project-stage-form';
	$formGuardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$leadstageId = isset($leadstages) && !empty(data_get($leadstages, 'id')) ? data_get($leadstages, 'id') : null;
		$updateBase = VW::PRJ_STG . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateParams = $leadstageId ? [$leadstageId] : ['#'];
		$updateUrl = ($updateResolved && $leadstageId) ? (route($updateResolved, $updateParams) ?? '#') : '#';
		$formId = 'update-project-stage-form';
		$formGuardMsg = Utility::fetchLinkMessage($lang, VW::PRJ_STG, 'update_project_stage_unavailable') ?? 'Update project stage route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in project_stages/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in project_stages/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in project_stages/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($leadstages) && !empty(data_get($leadstages, 'id')))
    <div class="{{ VC::CD }} bg-none card-box">
        {!! Form::model($leadstages, [
            'url'            => $updateUrl,
            'method'         => 'PUT',
            'id'             => $formId,
            'data-guard-msg' => $formGuardMsg,
        ]) !!}
            <div class="{{ VC::RW }}">
                <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                    {{ Form::label('name', __('Project Stage Name'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                </div>
                <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                    {{ Form::label('color', __('Color'), ['class' => VC::FM_LB]) }}
                    <input class="jscolor {{ VC::FM_CT }}" value="{{ data_get($leadstages, 'color', 'FFFFFF') }}" name="color" id="color" required>
                    <small class="small">{{ __('For chart representation') }}</small>
                </div>
                <div class="{{ VC::C12 }} text-end">
                    <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
                    <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
                </div>
            </div>
            <script defer src="{{ asset('assets/js/routes/projects/stages/update.js') }}"></script>
        {!! Form::close() !!}
    </div>
@else
    <div class="{{ VC::ALERT }} {{ VC::ALERT_DANGER }} {{ VC::M0 }}" role="alert">
        {{ __('No valid project stage information available.') }}
    </div>
@endif
