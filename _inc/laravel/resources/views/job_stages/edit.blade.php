@php
$lang ??= 'en';
	$hasJobStage ??= false;
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateResolved ??= null;
	$updateUrl ??= '#';
	$updateGuard ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$hasJobStage = !empty($jobStage ?? null) && data_get($jobStage, 'id');
		$updateBase = VW::JB_STG . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateUrl = ($updateResolved && $hasJobStage) ? (route($updateResolved, data_get($jobStage ?? null, 'id')) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::JB_STG, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
	} catch (\Error $e) {
		Log::error('Error in job_stages/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in job_stages/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in job_stages/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if(!$hasJobStage)
    <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ __('The requested job stage was not found or is unavailable.') }}</div>
@else
    {{ Form::model($jobStage, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'jobStage-edit-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('title', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter stage title')]) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/jobs/stages/edit.js') }}"></script>
    {{ Form::close() }}
@endif
