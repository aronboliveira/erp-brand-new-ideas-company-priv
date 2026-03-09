@php
$lang ??= 'en';
	$formId ??= 'ovt-store-form';
	$storeBase ??= '';
	$storeKebab ??= '';
	$storeRes ??= null;
	$storeUrl ??= '#';
	$storeGuard ??= '';
	$employeeId ??= '';
	$titleErr ??= false;
	$titleAttrs ??= [];
	$daysErr ??= false;
	$daysAttrs ??= [];
	$hoursErr ??= false;
	$hoursAttrs ??= [];
	$rateErr ??= false;
	$rateAttrs ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$storeBase = VW::OVT;
		$storeKebab = Str::kebab($storeBase);
		$storeRes = Route::has($storeBase) ? $storeBase : (Route::has($storeKebab) ? $storeKebab : null);
		$storeUrl = $storeRes ? (route($storeRes) ?? '#') : '#';
		$storeGuard = Utility::fetchLinkMessage($lang, VW::OVT, 'overtime_store_route_unavailable')
			?? __('Overtime store route is unavailable. Please contact technical support or your domain administrator.');
		$employeeId = (string) data_get($employee ?? null, 'id', '');
		$titleErr = !empty($errors) && method_exists($errors, 'has') && $errors->has('title');
		$titleAttrs = [
			'id' => 'title',
			'class' => trim(VC::FM_CT . ' ' . ($titleErr ? 'is-invalid' : '')),
			'required' => 'required',
			'aria-invalid' => $titleErr ? 'true' : 'false',
			'aria-describedby' => $titleErr ? 'title-error' : null,
			'autocomplete' => 'off',
		];
		$daysErr = !empty($errors) && method_exists($errors, 'has') && $errors->has('number_of_days');
		$daysAttrs = [
			'id' => 'number_of_days',
			'class' => trim(VC::FM_CT . ' ' . ($daysErr ? 'is-invalid' : '')),
			'required' => 'required',
			'aria-invalid' => $daysErr ? 'true' : 'false',
			'aria-describedby' => $daysErr ? 'number_of_days-error' : null,
			'step' => '0.01',
			'min' => '0',
			'inputmode' => 'decimal',
		];
		$hoursErr = !empty($errors) && method_exists($errors, 'has') && $errors->has('hours');
		$hoursAttrs = [
			'id' => 'hours',
			'class' => trim(VC::FM_CT . ' ' . ($hoursErr ? 'is-invalid' : '')),
			'required' => 'required',
			'aria-invalid' => $hoursErr ? 'true' : 'false',
			'aria-describedby' => $hoursErr ? 'hours-error' : null,
			'step' => '0.01',
			'min' => '0',
			'inputmode' => 'decimal',
		];
		$rateErr = !empty($errors) && method_exists($errors, 'has') && $errors->has('rate');
		$rateAttrs = [
			'id' => 'rate',
			'class' => trim(VC::FM_CT . ' ' . ($rateErr ? 'is-invalid' : '')),
			'required' => 'required',
			'aria-invalid' => $rateErr ? 'true' : 'false',
			'aria-describedby' => $rateErr ? 'rate-error' : null,
			'step' => '0.01',
			'min' => '0',
			'inputmode' => 'decimal',
		];
	} catch (\Error $e) {
		Log::error('Error in overtimes/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in overtimes/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in overtimes/create.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp

{{ Form::open([
    'url'               => $storeUrl,
    'method'            => 'POST',
    'id'                => $formId,
    'data-url'          => $storeUrl,
    'data-guard-msg'    => $storeGuard,
    'data-sv-localized' => 'true',
]) }}
    <div class="modal-body">
        {{ Form::hidden('employee_id', $employeeId, ['id' => 'employee_id']) }}

        <div class="{{ VC::RW }}">
            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('title', __('Overtime Title'), ['class' => VC::FM_LB]) }} <span class="{{ VC::TX_DNG }}">*</span>
                {{ Form::text('title', null, $titleAttrs) }}
                @error('title')
                    <span id="title-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('number_of_days', __('Number of days'), ['class' => VC::FM_LB]) }}
                {{ Form::number('number_of_days', null, $daysAttrs) }}
                @error('number_of_days')
                    <span id="number_of_days-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('hours', __('Hours'), ['class' => VC::FM_LB]) }}
                {{ Form::number('hours', null, $hoursAttrs) }}
                @error('hours')
                    <span id="hours-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>

            <div class="{{ VC::FM_GCB6 }}">
                {{ Form::label('rate', __('Rate'), ['class' => VC::FM_LB]) }}
                {{ Form::number('rate', null, $rateAttrs) }}
                @error('rate')
                    <span id="rate-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                @enderror
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
        <input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
    </div>
    <script defer src="{{ asset('assets/js/routes/overtimes/store.js') }}"></script>
{{ Form::close() }}
