@php
$lang ??= 'en';
	$hasJobCategory ??= false;
	$updateBase ??= '';
	$updateKebab ??= '';
	$updateResolved ??= null;
	$updateUrl ??= '#';
	$updateGuard ??= '';
	$jobCategoryId ??= null;
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$jobCategoryId = data_get($jobCategory ?? null, 'id');
		$hasJobCategory = !empty($jobCategory ?? null) && $jobCategoryId;
		$updateBase = VW::JB_CAT . '.update';
		$updateKebab = Str::kebab($updateBase);
		$updateResolved = Route::has($updateBase) ? $updateBase : (Route::has($updateKebab) ? $updateKebab : null);
		$updateUrl = ($updateResolved && $jobCategoryId) ? (route($updateResolved, $jobCategoryId) ?? '#') : '#';
		$updateGuard = Utility::fetchLinkMessage($lang, VW::JB_CAT, 'update_route_unavailable') ?? __('Update route is unavailable. Please contact technical support or your domain administrator.');
	} catch (\Error $e) {
		Log::error('Error in job_categories/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in job_categories/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in job_categories/edit.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if(!$hasJobCategory)
    <div class="{{ VC::ALT_WRN_MB0 }}" role="alert">{{ __('The requested job category was not found or is unavailable.') }}</div>
@else
    {{ Form::model($jobCategory, [
        'url'               => $updateUrl,
        'method'            => 'PUT',
        'id'                => 'jobCategory-edit-form',
        'data-url'          => $updateUrl,
        'data-guard-msg'    => $updateGuard,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="row">
                <div class="{{ VC::FM_GCB12 }}">
                    {{ Form::label('title', __('Title'), ['class' => VC::FM_LB]) }}
                    {{ Form::text('title', null, ['class' => VC::FM_CT, 'placeholder' => __('Enter category title')]) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/jobs/categories/edit.js') }}"></script>
    {{ Form::close() }}
@endif
