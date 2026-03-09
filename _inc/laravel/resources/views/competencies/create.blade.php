@php
$lang ??= 'en';
	$storeName ??= '';
	$storeRoute ??= '#';
	$storeFormId ??= 'competencyStoreForm';
	$storeGuardMsg ??= '';
	$createFormId ??= 'competency-create-form';
	$updateFormId ??= 'competency-update-form';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$storeName = ViewsConstants::CPT;
		$storeRoute = Route::has($storeName) ? (route($storeName) ?? '#') : '#';
		$storeGuardMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::CPT,
			'competency_store_route_unavailable'
		) ?? 'Competency store route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in competencies/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in competencies/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in competencies/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{{ Form::open([
    'url'            => $storeRoute,
    'method'         => 'post',
    'id'             => $storeFormId,
    'data-url'       => $storeRoute,
    'data-guard-msg' => $storeGuardMsg,
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
                    @if(!empty($performance) && ((is_array($performance) && count($performance)) || ($performance instanceof Collection && $performance->isNotEmpty())))
                        {{ Form::select('type', $performance, null, ['class' => VC::FM_CT . ' select amount_type', 'required' => 'required']) }}
                    @else
                        {{ Form::select('type', [__('No Performance option found')], null, ['class' => VC::FM_CT . ' select amount_type', 'required' => 'required', 'disabled' => 'disabled']) }}
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
{{ Form::close() }}
@if(!empty($competencies) && ((is_array($competencies) && count($competencies)) || ($competencies instanceof Collection && $competencies->isNotEmpty())))
    @php
		$updateName ??= '';
		$updateRoute ??= '#';
		$updateFormId ??= 'competencyUpdateForm';
		$updateGuardMsg ??= '';
		try {
			$updateName = ViewsConstants::CPT . '.update';
			$updateRoute = Route::has($updateName) ? (route($updateName, $competencies->id) ?? '#') : '#';
			$updateFormId = 'competencyUpdateForm_' . $competencies->id;
			$updateGuardMsg = Utility::fetchLinkMessage(
				$lang,
				ViewsConstants::CPT,
				'competency_update_route_unavailable'
			) ?? 'Competency update route is unavailable. Please contact technical support or your domain administrator.';
		} catch (\Error $e) {
			Log::error('Error in competencies/create.blade.php update @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\Exception $e) {
			Log::error('Exception in competencies/create.blade.php update @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		} catch (\Throwable $e) {
			Log::error('Throwable in competencies/create.blade.php update @php block', [
				'exception_class' => get_class($e),
				'message' => $e->getMessage(),
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			]);
		}
@endphp
    {{ Form::model($competencies, [
        'route'          => [$updateRoute],
        'method'         => 'PUT',
        'id'             => $updateFormId,
        'data-url'       => $updateRoute,
        'data-guard-msg' => $updateGuardMsg,
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
                        @if(!empty($performance) && ((is_array($performance) && count($performance)) || ($performance instanceof Collection && $performance->isNotEmpty())))
                            {{ Form::select('type', $performance, null, ['class' => VC::FM_CT . ' select amount_type', 'required' => 'required']) }}
                        @else
                            {{ Form::select('type', [__('No Performance option found')], null, ['class' => VC::FM_CT . ' select amount_type', 'required' => 'required', 'disabled' => 'disabled']) }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
    {{ Form::close() }}
@else
    <div class="{{ VC::TXT_MT }}">{{ __('No competencies list found') }}</div>
@endif
<script defer>
    window.RouteGuard?.guardFormSubmit?.('{{ $createFormId }}');
    window.RouteGuard?.guardFormSubmit?.('{{ $updateFormId }}');
</script>
