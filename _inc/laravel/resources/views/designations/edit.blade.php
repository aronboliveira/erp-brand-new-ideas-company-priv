@php
$lang ??= 'en';
	$designationId ??= '';
	$departmentsIsList ??= false;
	$departmentsOptions ??= [];
	$designationUpdateBase ??= '';
	$designationUpdateKebab ??= '';
	$designationUpdateResolved ??= null;
	$designationUpdateUrl ??= '#';
	$designationUpdateFormId ??= 'designation-update-form-x';
	$designationGuardMsg ??= '';
	$deptHasError ??= false;
	$deptAttrs ??= [];
	$nameHasError ??= false;
	$nameAttrs ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? 'en';
		$designationId = (string) data_get($designation ?? null, 'id', '');
		$departmentsIsList = (is_array($departments ?? null) && count($departments ?? [])) || (($departments ?? null) instanceof Collection && $departments->isNotEmpty());
		$departmentsOptions = $departmentsIsList ? $departments : ['' => __('No departments available')];
		$designationUpdateBase = VW::DSG . '.update';
		$designationUpdateKebab = Str::kebab($designationUpdateBase);
		$designationUpdateResolved = Route::has($designationUpdateBase) ? $designationUpdateBase : (Route::has($designationUpdateKebab) ? $designationUpdateKebab : null);
		$designationUpdateUrl = ($designationUpdateResolved && $designationId !== '') ? (route($designationUpdateResolved, [$designationId]) ?? '#') : '#';
		$designationUpdateFormId = 'designation-update-form-' . ($designationId === '' ? 'x' : $designationId);
		$designationGuardMsg = Utility::fetchLinkMessage($lang, VW::DSG, 'designation_update_route_unavailable') ?? 'Update designation route is unavailable. Please contact technical support or your domain administrator.';
		$deptHasError = !empty($errors) && method_exists($errors, 'has') && $errors->has('department_id');
		$deptAttrs = [
			'id' => 'department_id',
			'class' => trim(VC::FM_CT_SL . ' ' . ($deptHasError ? 'is-invalid' : '')),
			'placeholder' => __('Select Department'),
			'required' => 'required',
			'aria-invalid' => $deptHasError ? 'true' : 'false',
			'aria-describedby' => $deptHasError ? 'department_id-error' : null,
		];
		if (!$departmentsIsList) { $deptAttrs['disabled'] = 'disabled'; }
		$nameHasError = !empty($errors) && method_exists($errors, 'has') && $errors->has('name');
		$nameAttrs = [
			'id' => 'name',
			'class' => trim(VC::FM_CT . ' ' . ($nameHasError ? 'is-invalid' : '')),
			'placeholder' => __('Enter Designation Name'),
			'required' => 'required',
			'aria-invalid' => $nameHasError ? 'true' : 'false',
			'aria-describedby' => $nameHasError ? 'name-error' : null,
			'autocomplete' => 'off',
		];
	} catch (\Error $e) {
		Log::error('Error in designations/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in designations/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in designations/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($designation) && isset($designation->id))
    {{ Form::model($designation, [
        'method'            => 'PUT',
        'url'               => $designationUpdateUrl,
        'id'                => $designationUpdateFormId,
        'data-url'          => $designationUpdateUrl,
        'data-guard-msg'    => $designationGuardMsg,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('department_id', __('Department'), ['class' => VC::FM_LB]) }}
                        {{ Form::select('department_id', $departmentsOptions, null, $deptAttrs) }}
                        @error('department_id')
                            <span id="department_id-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                        @enderror
                    </div>
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('name', __('Name'), ['class' => VC::FM_LB]) }}
                        {{ Form::text('name', null, $nameAttrs) }}
                        @error('name')
                            <span id="name-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <input type="button" value="{{ __('Cancel') }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
            <input type="submit" value="{{ __('Update') }}" class="{{ VC::BT_PRM }}">
        </div>
        <script defer src="{{ asset('assets/js/routes/designations/update.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="{{ VC::TXCT }} {{ VC::P4 }}">
        <i class="ti ti-alert-circle icon-lg {{ VC::MB3 }}"></i>
        <h5>{{ __('Something went wrong') }}</h5>
        <p class="{{ VC::TXT_MT }}">{{ __('Designation information is not available. Please try again later.') }}</p>
        <button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ __('Close') }}</button>
    </div>
@endif
