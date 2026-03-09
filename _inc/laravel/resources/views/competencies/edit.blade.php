@php
$lang ??= 'en';
	$routeName ??= '';
	$updateRoute ??= '#';
	$formId ??= 'competencyUpdateForm';
	$guardMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$routeName = ViewsConstants::CPT . '.update';
		$competencyId = data_get($competencies ?? null, 'id');
		$updateRoute = $competencyId && Route::has($routeName)
			? (route($routeName, $competencyId) ?? '#')
			: ($competencyId && Route::has(Str::kebab($routeName))
				? (route(Str::kebab($routeName), $competencyId) ?? '#')
				: '#');
		$formId = 'competencyUpdateForm_' . ($competencyId ?: 'unknown');
		$guardMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::CPT,
			'competency_update_route_unavailable'
		) ?? 'Competency update route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in competencies/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in competencies/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in competencies/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

@if(!empty($competencies) && ((is_array($competencies) && count($competencies)) || ($competencies instanceof Collection && $competencies->isNotEmpty())))
    {{ Form::model($competencies, [
        'route'          => [$updateRoute],
        'method'         => 'PUT',
        'id'             => $formId,
        'data-url'       => $updateRoute,
        'data-guard-msg' => $guardMsg,
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                        {{ Form::text('name', null, ['class' => VC::FM_CT, 'required' => 'required']) }}
                    </div>
                </div>
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('type', __('Type'), ['class' => VC::FM_LB]) }}
                        {{ Form::select('type', $performance, null, ['class' => VC::FM_CT . ' select', 'required' => 'required']) }}
                    </div>
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
    <div class="{{ VC::TXT_MT }}">{{ __('No competencies found') }}</div>
@endif
