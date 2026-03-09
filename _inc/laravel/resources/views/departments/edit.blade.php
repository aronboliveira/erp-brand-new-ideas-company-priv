@php
$departmentId ??= '';
	$branchIsList ??= false;
	$branchOptions ??= [];
	$departmentUpdateBaseRouteName ??= '';
	$departmentUpdateKebabRouteName ??= '';
	$departmentUpdateResolvedName ??= null;
	$departmentUpdateUrl ??= '#';
	$departmentUpdateFormId ??= 'department-update-form-x';
	$langValue ??= 'en';
	$departmentUpdateGuardMessage ??= '';
	$branchHasError ??= false;
	$branchAttrs ??= [];
	$nameHasError ??= false;
	$nameAttrs ??= [];
	try {
		$departmentId = (string) data_get($department ?? null, 'id', '');
		$branchIsList = (is_array($branch ?? null) && count($branch ?? [])) || (($branch ?? null) instanceof Collection && $branch->isNotEmpty());
		$branchOptions = $branchIsList ? $branch : ['' => __('No branches available')];
		$departmentUpdateBaseRouteName = VW::DPT . '.update';
		$departmentUpdateKebabRouteName = Str::kebab($departmentUpdateBaseRouteName);
		$departmentUpdateResolvedName = Route::has($departmentUpdateBaseRouteName) ? $departmentUpdateBaseRouteName : (Route::has($departmentUpdateKebabRouteName) ? $departmentUpdateKebabRouteName : null);
		$departmentUpdateUrl = ($departmentUpdateResolvedName && $departmentId !== '') ? (route($departmentUpdateResolvedName, [$departmentId]) ?? '#') : '#';
		$departmentUpdateFormId = 'department-update-form-' . ($departmentId === '' ? 'x' : $departmentId);
		$langValue = Utility::fetchUserLang() ?? 'en';
		$departmentUpdateGuardMessage = Utility::fetchLinkMessage($langValue, VW::DPT, 'update_department_route_unavailable') ?? 'Update department route is unavailable. Please contact technical support or your domain administrator.';
		$branchHasError = !empty($errors) && method_exists($errors, 'has') && $errors->has('branch_id');
		$branchAttrs = [
			'id' => 'branch_id',
			'class' => trim(VC::FM_CT_SL . ' ' . ($branchHasError ? 'is-invalid' : '')),
			'placeholder' => __('Select Branch'),
			'required' => 'required',
			'aria-invalid' => $branchHasError ? 'true' : 'false',
			'aria-describedby' => $branchHasError ? 'branch_id-error' : null,
		];
		if (!$branchIsList) { $branchAttrs['disabled'] = 'disabled'; }
		$nameHasError = !empty($errors) && method_exists($errors, 'has') && $errors->has('name');
		$nameAttrs = [
			'id' => 'name',
			'class' => trim(VC::FM_CT . ' ' . ($nameHasError ? 'is-invalid' : '')),
			'placeholder' => __('Enter Department Name'),
			'required' => 'required',
			'aria-invalid' => $nameHasError ? 'true' : 'false',
			'aria-describedby' => $nameHasError ? 'name-error' : null,
			'autocomplete' => 'off',
		];
	} catch (\Error $e) {
		Log::error('Error in departments/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in departments/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in departments/edit.blade.php main @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
@if(!empty($department) && isset($department->id))
    {{ Form::model($department, [
        'method'            => 'PUT',
        'url'               => $departmentUpdateUrl,
        'id'                => $departmentUpdateFormId,
        'data-url'          => $departmentUpdateUrl,
        'data-guard-msg'    => $departmentUpdateGuardMessage,
        'data-sv-localized' => 'true',
    ]) }}
        <div class="modal-body">
            <div class="{{ VC::RW }}">
                <div class="{{ VC::C12 }}">
                    <div class="{{ VC::FM_G }}">
                        {{ Form::label('branch_id', __('Branch'), ['class' => VC::FM_LB]) }}
                        {{ Form::select('branch_id', $branchOptions, null, $branchAttrs) }}
                        @error('branch_id')
                            <span id="branch_id-error" class="{{ VC::INV_FB }} {{ VC::DBL }}" role="alert"><strong class="{{ VC::TX_DNG }}">{{ $message }}</strong></span>
                        @enderror
                    </div>
                </div>
                <div class="{{ VC::C12 }}">
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
        <script defer src="{{ asset('assets/js/routes/departments/update.js') }}"></script>
    {{ Form::close() }}
@else
    <div class="modal-body">
        <h6>{{ __('Department not found.') }}</h6>
    </div>
@endif
