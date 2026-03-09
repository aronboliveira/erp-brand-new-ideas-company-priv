@php
$lang ??= 'en';
	$storeRoute ??= '#';
	$formId ??= 'contract-type-store-form';
	$storeMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$storeRoute = Route::has(ViewsConstants::CTC_TP)
			? (route(ViewsConstants::CTC_TP) ?? '#')
			: (Route::has(Str::kebab(ViewsConstants::CTC_TP))
				? (route(Str::kebab(ViewsConstants::CTC_TP)) ?? '#')
				: '#');
		$storeMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::CTC_TP,
			'contract_type_store_route_unavailable'
		) ?? 'Contract Type store route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in contract_types/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in contract_types/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in contract_types/create.blade.php main @php block', [
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
    'id'             => $formId,
    'data-url'       => $storeRoute,
    'data-guard-msg' => $storeMsg,
]) }}
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }}">
                {{ Form::label('name', __('Name')) }}
                {{ Form::text('name', '', ['class' => VC::FM_CT, 'required' => 'required']) }}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/contractTypes/store.js') }}"></script>
{{ Form::close() }}
