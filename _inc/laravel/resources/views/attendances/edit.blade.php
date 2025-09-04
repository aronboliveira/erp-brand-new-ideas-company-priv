@php
	use App\Config\Constants\{StacksConstants, ViewClassNamesConstants as VC, ViewsConstants};
	use App\Models\Utility;
	use Collective\Html\FormFacade as Form;
	use Illuminate\Support\{Facades\Log, Facades\Route, Str};
	use InvalidArgumentException;

	$employees ??= [];
	$EmployeeAttendance ??= null;

	$lang = Utility::fetchUserLang();
	$formId = 'update-employee-attendance-form';
	$empAtdUpdateBaseName = ViewsConstants::EMP_ATD . '.update';
	$empAtdUpdateResolved = null;
	$empAtdUpdateActionUrl = '#';
	$empAtdUpdateGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::EMP_ATD, 'update_employee_attendance_route_unavailable') ?? 'Update employee attendance route is unavailable. Please contact technical support or your domain administrator.';
	$attendanceId = data_get($EmployeeAttendance, 'id') ?? null;

	try {
		$empAtdUpdateResolved = Route::has($empAtdUpdateBaseName)
			? $empAtdUpdateBaseName
			: (Route::has(Str::kebab($empAtdUpdateBaseName)) ? Str::kebab($empAtdUpdateBaseName) : null);
	} catch (\Error $e) {
		Log::error('Blade employee_attendance/update: route name resolution error: ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade employee_attendance/update: invalid argument while resolving route name: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade employee_attendance/update: general exception while resolving route name: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade employee_attendance/update: throwable while resolving route name: ' . $e->getMessage());
	}

	try {
		$empAtdUpdateActionUrl = ($empAtdUpdateResolved && !empty($attendanceId)) ? route($empAtdUpdateResolved, $attendanceId) : '#';
	} catch (\Error $e) {
		Log::error('Blade employee_attendance/update: route URL generation error: ' . $e->getMessage());
		$empAtdUpdateActionUrl = '#';
	} catch (InvalidArgumentException $e) {
		Log::error('Blade employee_attendance/update: invalid argument while generating URL: ' . $e->getMessage());
		$empAtdUpdateActionUrl = '#';
	} catch (\Exception $e) {
		Log::error('Blade employee_attendance/update: general exception while generating URL: ' . $e->getMessage());
		$empAtdUpdateActionUrl = '#';
	} catch (\Throwable $e) {
		Log::error('Blade employee_attendance/update: throwable while generating URL: ' . $e->getMessage());
		$empAtdUpdateActionUrl = '#';
	}

	$cancelLabel = __('Cancel') ?: __('Failed to get cancel label');
	$updateLabel = __('Update') ?: __('Failed to get update label');
@endphp

@if(!empty($EmployeeAttendance) && isset($EmployeeAttendance?->id))
	{{ Form::model($EmployeeAttendance, [
		'url'                  => $empAtdUpdateActionUrl,
		'method'               => 'PUT',
		'id'                   => $formId,
		'data-resolved-action' => $empAtdUpdateActionUrl,
		'data-guard-msg'       => $empAtdUpdateGuardMsg,
		'data-sv-localized'    => 'true',
	]) }}
		<div class="modal-body">
			<div class="row">
				@foreach(($fields ?? []) as $f)
					@php
						$fname = data_get($f, 'name') ?? 'unknown';
						$ftype = data_get($f, 'type') ?? 'text';
						$flabel = data_get($f, 'label') ?? __('No label available');
						$fclass = data_get($f, 'class') ?? 'form-control';
						$fopts = data_get($f, 'options') ?? [];
						$fcol = data_get($f, 'col') ?? 'col-lg-6';
					@endphp
					<div class="form-group {{ $fcol }}">
						{{ Form::label($fname, $flabel, ['class' => 'form-label']) }}
						@if($ftype === 'select')
							{{ Form::select($fname, $fopts, null, ['class' => $fclass]) }}
						@elseif($ftype === 'time')
							{{ Form::time($fname, null, ['class' => $fclass]) }}
						@elseif($ftype === 'date')
							{{ Form::date($fname, null, ['class' => $fclass]) }}
						@else
							{{ Form::text($fname, null, ['class' => $fclass]) }}
						@endif
					</div>
				@endforeach
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">{{ $cancelLabel }}</button>
			<button type="submit" class="{{ VC::BT_PRM }}">{{ $updateLabel }}</button>
		</div>
		<script defer src="{{ asset('assets/js/routes/attendances/update.js') }}"></script>
	{{ Form::close() }}
@else
	<div class="modal-body">
		<div class="row">
			<div class="col-md-12">
				<div class="{{ VC::ALERT }} {{ VC::ALERT_DANGER }}">
					<h4 class="text-danger">{{ __('No Attendance Record Found') }}</h4>
					<p>{{ __('The attendance record data is invalid or not found. Please refresh the page and try again.') }}</p>
				</div>
			</div>
		</div>
	</div>
@endif