@php
$lang ??= 'en';
	$storeRoute ??= '#';
	$formId ??= 'awardtype-store-form';
	$storeMsg ??= '';
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$storeRoute = Route::has(ViewsConstants::AWD_TP)
			? (route(ViewsConstants::AWD_TP) ?? '#')
			: (Route::has(Str::kebab(ViewsConstants::AWD_TP))
				? (route(Str::kebab(ViewsConstants::AWD_TP)) ?? '#')
				: '#');
		$storeMsg = Utility::fetchLinkMessage(
			$lang,
			ViewsConstants::AWD_TP,
			'award_type_store_route_unavailable'
		) ?? 'Award Type store route is unavailable. Please contact technical support or your domain administrator.';
	} catch (\Error $e) {
		Log::error('Error in award_types/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in award_types/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in award_types/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::open([
    'url'               => $storeRoute,
    'method'            => 'post',
    'id'                => $formId,
    'data-url'          => $storeRoute,
    'data-sv-localized' => 'true',
    'data-guard-msg'    => $storeMsg,
]) }}
    <div class="modal-body">
        <div class="{{ C::RW }}">
            <div class="{{ VC::CM12 }}">
                <div class="{{ C::FM_GB3 }}">
                    {{ Form::label('name', __('Name'), ['class'=>C::FM_LB]) }}<span class="{{ VC::TX_DNG }}">*</span>
                    {{ Form::text('name', null, ['class'=>C::FM_CT,'placeholder'=>__('Enter Award Type Name')]) }}
                    @error('name')
                        <span class="{{ VC::TX_DNG }}">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="{{ C::BT_LG }}" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
        <button type="submit" class="{{ C::BT_PRM }}">{{ __('Create') }}</button>
    </div>
    <script defer src="{{ asset('assets/js/routes/awardTypes/store.js') }}"></script>
{{ Form::close() }}
