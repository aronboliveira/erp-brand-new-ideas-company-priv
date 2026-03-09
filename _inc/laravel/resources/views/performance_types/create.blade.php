@php
$lang ??= 'en';
	$formId ??= 'pfm-tp-store-form';
	$base ??= '';
	$baseKebab ??= '';
	$routeRes ??= null;
	$actionUrl ??= '#';
	$guardMsg ??= '';
	$nameErr ??= false;
	$nameAttrs ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$base = VW::PFM_TP;
		$baseKebab = Str::kebab($base);
		$routeRes = Route::has($base) ? $base : (Route::has($baseKebab) ? $baseKebab : null);
		$actionUrl = $routeRes ? (route($routeRes) ?? '#') : '#';
		$guardMsg = Utility::fetchLinkMessage($lang, VW::PFM_TP, 'store_route_unavailable')
			?? __('Performance type route is unavailable. Please contact technical support or your domain administrator.');
		$nameErr = !empty($errors) && method_exists($errors, 'has') && $errors->has('name');
		$nameAttrs = [
			'id' => 'name',
			'class' => trim(VC::FM_CT . ' ' . ($nameErr ? 'is-invalid' : '')),
			'required' => 'required',
			'aria-invalid' => $nameErr ? 'true' : 'false',
			'aria-describedby' => $nameErr ? 'name-error' : null,
			'autocomplete' => 'off',
		];
	} catch (\Error $e) {
		Log::error('Error in performance_types/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in performance_types/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in performance_types/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::open([
    'url'               => $actionUrl,
    'id'                => $formId,
    'data-url'          => $actionUrl,
    'data-guard-msg'    => $guardMsg,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        <div class="{{ VC::FM_GCB12 }}">
            {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
            {{ Form::text('name', null, $nameAttrs) }}
            @error('name')
                <span id="name-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
            @enderror
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/performanceTypes/store.js') }}"></script>
{{ Form::close() }}
