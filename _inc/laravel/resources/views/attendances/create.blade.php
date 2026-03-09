@php
$employees ??= [];
	$lang = Utility::fetchUserLang();
	$formId = 'store-employee-attendance-form';
	$empAtdBaseName = ViewsConstants::EMP_ATD;
	$empAtdResolved = null;
	$empAtdActionUrl = '#';
	$empAtdGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::EMP_ATD, 'store_employee_attendance_route_unavailable') ?? 'Store employee attendance route is unavailable. Please contact technical support or your domain administrator.';
	try {
		$empAtdResolved = Route::has($empAtdBaseName)
			? $empAtdBaseName
			: (Route::has(Str::kebab($empAtdBaseName)) ? Str::kebab($empAtdBaseName) : null);
	} catch (\Error $e) {
		Log::error('Blade employee_attendance/create: route name resolution error: ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade employee_attendance/create: invalid argument while resolving route name: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade employee_attendance/create: general exception while resolving route name: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade employee_attendance/create: throwable while resolving route name: ' . $e->getMessage());
	}
	try {
		$empAtdActionUrl = $empAtdResolved ? route($empAtdResolved) : '#';
	} catch (\Error $e) {
		Log::error('Blade employee_attendance/create: route URL generation error: ' . $e->getMessage());
		$empAtdActionUrl = '#';
	} catch (InvalidArgumentException $e) {
		Log::error('Blade employee_attendance/create: invalid argument while generating URL: ' . $e->getMessage());
		$empAtdActionUrl = '#';
	} catch (\Exception $e) {
		Log::error('Blade employee_attendance/create: general exception while generating URL: ' . $e->getMessage());
		$empAtdActionUrl = '#';
	} catch (\Throwable $e) {
		Log::error('Blade employee_attendance/create: throwable while generating URL: ' . $e->getMessage());
		$empAtdActionUrl = '#';
	}

	$fields = [
		[
			'name'    => 'employee_id',
			'type'    => 'select',
			'label'   => __('Employee') ?: __('No employee label available'),
			'options' => $employees,
			'class'   => 'form-control select2',
		],
		[
			'name'  => 'date',
			'type'  => 'text',
			'label' => __('Date') ?: __('No date label available'),
			'class' => 'form-control datepicker',
		],
		[
			'name'  => 'clock_in',
			'type'  => 'time',
			'label' => __('Clock In') ?: __('No clock in label available'),
			'class' => 'form-control',
		],
		[
			'name'  => 'clock_out',
			'type'  => 'time',
			'label' => __('Clock Out') ?: __('No clock out label available'),
			'class' => 'form-control',
		],
	];

	$cancelLabel = __('Cancel') ?: __('Failed to get cancel label');
	$createLabel = __('Create') ?: __('Failed to get create label');
@endphp

{{ Form::open([
	'url'                  => $empAtdActionUrl,
	'method'               => 'post',
	'id'                   => $formId,
	'data-resolved-action' => $empAtdActionUrl,
	'data-guard-msg'       => $empAtdGuardMsg,
	'data-sv-localized'    => 'true',
]) }}
	<div class="{{ VC::CD_BD }} p-0">
		<div class="{{ VC::RW }}">
			@foreach(($fields ?? []) as $f)
				@php
					try {
					    $fname = data_get($f, 'name') ?? 'unknown';
					    $ftype = data_get($f, 'type') ?? 'text';
					    $flabel = data_get($f, 'label') ?? __('No label available');
					    $fclass = data_get($f, 'class') ?? 'form-control';
					    $fopts = data_get($f, 'options') ?? [];
					} catch (\Throwable $e) {
					    \Log::error('attendances/create — ' . get_class($e) . ': ' . $e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
					}
@endphp
				<div class="{{ VC::FM_G }} {{ VC::C6 }}">
					{{ Form::label($fname, $flabel, ['class' => VC::FM_LB]) }}
					@if($ftype === 'select')
						{{ Form::select($fname, $fopts, null, ['class' => $fclass]) }}
					@elseif($ftype === 'time')
						{{ Form::time($fname, null, ['class' => $fclass]) }}
					@else
						{{ Form::text($fname, null, ['class' => $fclass]) }}
					@endif
				</div>
			@endforeach
		</div>
	</div>
	<div class="modal-footer">
		<button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ $cancelLabel }}</button>
		{{ Form::submit($createLabel, ['class' => VC::BT_PRM]) }}
	</div>
	<script defer src="{{ asset('assets/js/routes/attendances/store.js') }}"></script>
{{ Form::close() }}
