@php
$lang ??= 'en';
	$formId ??= 'prd-sv-unt-store-form';
	$base ??= '';
	$baseKebab ??= '';
	$routeRes ??= null;
	$actionUrl ??= '#';
	$guardMsg ??= '';
	$nameErr ??= false;
	$nameAttr ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$base = VW::PRD_SV_UNT;
		$baseKebab = Str::kebab($base);
		$routeRes = Route::has($base) ? $base : (Route::has($baseKebab) ? $baseKebab : null);
		$actionUrl = $routeRes ? (route($routeRes) ?? '#') : '#';
		$guardMsg = Utility::fetchLinkMessage($lang, VW::PRD_SV_UNT, 'store_route_unavailable')
			?? __('Product service unit store route is unavailable. Please contact technical support or your domain administrator.');
		$nameErr = !empty($errors) && method_exists($errors, 'has') && $errors->has('name');
		$nameAttr = [
			'id' => 'name',
			'class' => trim(VC::FM_CT . ($nameErr ? ' is-invalid' : '')),
			'required' => 'required',
			'aria-invalid' => $nameErr ? 'true' : 'false',
			'aria-describedby' => $nameErr ? 'name-error' : null,
			'autocomplete' => 'off',
		];
	} catch (\Error $e) {
		Log::error('Error in product_service_units/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in product_service_units/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in product_service_units/create.blade.php main @php block', [
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
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB12 }}">
                {{ Form::label('name', __('Unit Name'), ['class' => VC::FM_LB]) }}
                {{ Form::text('name', null, $nameAttr) }}
                @error('name')
                    <span id="name-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/products/services/units/store.js') }}"></script>
{{ Form::close() }}
