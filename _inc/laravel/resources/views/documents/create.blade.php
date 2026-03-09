@php
$lang ??= 'en';
	$docStoreBase ??= '';
	$docStoreKebab ??= '';
	$docStoreResolved ??= null;
	$docStoreUrl ??= '#';
	$docStoreFormId ??= 'document-store-form';
	$docStoreGuardMsg ??= '';
	$nameHasError ??= false;
	$nameAttrs ??= [];
	$reqHasError ??= false;
	$isRequiredOptions ??= [];
	$reqAttrs ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$docStoreBase = VW::DOC;
		$docStoreKebab = Str::kebab($docStoreBase);
		$docStoreResolved = Route::has($docStoreBase) ? $docStoreBase : (Route::has($docStoreKebab) ? $docStoreKebab : null);
		$docStoreUrl = $docStoreResolved ? (route($docStoreResolved) ?? '#') : '#';
		$docStoreGuardMsg = Utility::fetchLinkMessage($lang, VW::DOC, 'document_store_route_unavailable')
			?? 'Store document route is unavailable. Please contact technical support or your domain administrator.';
		$nameHasError = !empty($errors) && method_exists($errors, 'has') && $errors->has('name');
		$nameAttrs = [
			'id' => 'doc_name',
			'class' => trim(VC::FM_CT . ' ' . ($nameHasError ? 'is-invalid' : '')),
			'placeholder' => __('Enter Document Name'),
			'required' => 'required',
			'autocomplete' => 'off',
			'aria-invalid' => $nameHasError ? 'true' : 'false',
			'aria-describedby' => $nameHasError ? 'name-error' : null,
		];
		$reqHasError = !empty($errors) && method_exists($errors, 'has') && $errors->has('is_required');
		$isRequiredOptions = ['0' => __('Not Required'), '1' => __('Is Required')];
		$reqAttrs = [
			'id' => 'doc_required',
			'class' => trim(VC::FM_CT . ' select2' . ($reqHasError ? ' is-invalid' : '')),
			'required' => 'required',
			'aria-invalid' => $reqHasError ? 'true' : 'false',
			'aria-describedby' => $reqHasError ? 'is_required-error' : null,
		];
	} catch (\Error $e) {
		Log::error('Error in documents/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in documents/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in documents/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{{ Form::open([
    'url'               => $docStoreUrl,
    'method'            => 'POST',
    'id'                => $docStoreFormId,
    'data-url'          => $docStoreUrl,
    'data-guard-msg'    => $docStoreGuardMsg,
    'data-sv-localized' => 'true',
]) }}
    @csrf
    <div class="modal-body">
        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('name', __('Name'), ['class' => VC::FM_LB, 'for' => 'doc_name']) }}
                {{ Form::text('name', null, $nameAttrs) }}
                @error('name')
                    <span id="name-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
            <div class="{{ VC::FM_G }} {{ VC::C12 }}">
                {{ Form::label('is_required', __('Required Field'), ['class' => VC::FM_LB, 'for' => 'doc_required']) }}
                {{ Form::select('is_required', $isRequiredOptions, null, $reqAttrs) }}
                @error('is_required')
                    <span id="is_required-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/documents/store.js') }}"></script>
{{ Form::close() }}
