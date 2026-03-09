@php
$branch ??= null;
	$errors ??= null;
	$lang ??= '';
	$branchIsList ??= false;
	$branchOptions ??= ['' => __('No branches available')];
	$departmentStoreBaseRouteName ??= VW::DPT;
	$departmentStoreKebabRouteName ??= '';
	$departmentStoreResolvedName ??= null;
	$departmentStoreUrl ??= '#';
	$departmentStoreFormId ??= 'department-store-form';
	$guardMsg ??= '';
	$branchHasError ??= false;
	$branchAttrs ??= [];
	$nameHasError ??= false;
	$nameAttrs ??= [];
	try {
		$lang = Utility::fetchUserLang() ?? '';
		$branchIsList = (is_array($branch) && count($branch))
			|| (($branch) instanceof Collection && $branch->isNotEmpty());
		$branchOptions = $branchIsList
			? (is_array($branch) ? $branch : $branch->toArray())
			: ['' => __('No branches available')];
		$departmentStoreKebabRouteName = Str::kebab($departmentStoreBaseRouteName);
		$departmentStoreResolvedName = Route::has($departmentStoreBaseRouteName)
			? $departmentStoreBaseRouteName
			: (Route::has($departmentStoreKebabRouteName) ? $departmentStoreKebabRouteName : null);
		$departmentStoreUrl = $departmentStoreResolvedName
			? (route($departmentStoreResolvedName) ?? '#')
			: '#';
		$guardMsg = Utility::fetchLinkMessage($lang, VW::DPT, 'store_department_route_unavailable')
			?? 'Store department route is unavailable. Please contact technical support or your domain administrator.';
		$branchHasError = !empty($errors) && method_exists($errors, 'has') && $errors->has('branch_id');
		$branchAttrs = [
			'id'               => 'branch_id',
			'class'            => trim(VC::FM_CT_SL . ' ' . ($branchHasError ? 'is-invalid' : '')),
			'placeholder'      => __('Select Branch'),
			'required'         => 'required',
			'aria-invalid'     => $branchHasError ? 'true' : 'false',
			'aria-describedby' => $branchHasError ? 'branch_id-error' : null,
		];
		if (!$branchIsList) {
			$branchAttrs['disabled'] = 'disabled';
		}
		$nameHasError = !empty($errors) && method_exists($errors, 'has') && $errors->has('name');
		$nameAttrs = [
			'id'               => 'name',
			'class'            => trim(VC::FM_CT . ' ' . ($nameHasError ? 'is-invalid' : '')),
			'placeholder'      => __('Enter Department Name'),
			'required'         => 'required',
			'aria-invalid'     => $nameHasError ? 'true' : 'false',
			'aria-describedby' => $nameHasError ? 'name-error' : null,
			'autocomplete'     => 'off',
		];
	} catch (\Error $e) {
		Log::error('Error in departments/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Exception $e) {
		Log::error('Exception in departments/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	} catch (\Throwable $e) {
		Log::error('Throwable in departments/create.blade.php @php block', [
			'exception_class' => get_class($e),
			'message' => $e->getMessage(),
			'file' => $e->getFile(),
			'line' => $e->getLine(),
		]);
	}
@endphp
{{ Form::open([
	'url'            => $departmentStoreUrl,
	'method'         => 'POST',
	'id'             => $departmentStoreFormId,
	'data-url'       => $departmentStoreUrl,
	'data-guard-msg' => $guardMsg,
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
		<input type="submit" value="{{ __('Create') }}" class="{{ VC::BT_PRM }}">
	</div>
	<script defer src="{{ asset('assets/js/routes/departments/store.js') }}"></script>
{{ Form::close() }}
