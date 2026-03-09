@php
$lang = Utility::fetchUserLang();
	$formId = 'import-employee-attendance-form';
	$importBaseName = ViewsConstants::EMP_ATD . '.import';
	$importResolved = null;
	$importActionUrl = '#';
	$importGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::EMP_ATD, 'import_employee_attendance_route_unavailable') ?? 'Import employee attendance route is unavailable. Please contact technical support or your domain administrator.';
	$sampleLinkId = 'employee-attendance-sample-download';
	$sampleGuardMsg = Utility::fetchLinkMessage($lang, ViewsConstants::EMP_ATD, 'download_sample_employee_attendance_unavailable') ?? 'Download sample employee attendance CSV is unavailable. Please contact technical support or your domain administrator.';
	$downloadLabel = __('Download') ?: __('Failed to get download label');
	$downloadSampleLabel = __('Download sample employee CSV file') ?: __('No sample label available');
	$selectCsvLabel = __('Select CSV File') ?: __('No csv label available');
	$cancelLabel = __('Cancel') ?: __('Failed to get cancel label');
	$uploadLabel = __('Upload') ?: __('Failed to get upload label');
	$sampleUrl = '#';
	try {
		$importResolved = Route::has($importBaseName)
			? $importBaseName
			: (Route::has(Str::kebab($importBaseName)) ? Str::kebab($importBaseName) : null);
	} catch (\Error $e) {
		Log::error('Blade employee_attendance/import: route name resolution error (Error): ' . $e->getMessage());
	} catch (InvalidArgumentException $e) {
		Log::error('Blade employee_attendance/import: invalid argument while resolving route name: ' . $e->getMessage());
	} catch (\Exception $e) {
		Log::error('Blade employee_attendance/import: general exception while resolving route name: ' . $e->getMessage());
	} catch (\Throwable $e) {
		Log::error('Blade employee_attendance/import: throwable while resolving route name: ' . $e->getMessage());
	}
	try {
		$importActionUrl = $importResolved ? route($importResolved) : '#';
	} catch (\Error $e) {
		Log::error('Blade employee_attendance/import: route URL generation error (Error): ' . $e->getMessage());
		$importActionUrl = '#';
	} catch (InvalidArgumentException $e) {
		Log::error('Blade employee_attendance/import: invalid argument while generating URL: ' . $e->getMessage());
		$importActionUrl = '#';
	} catch (\Exception $e) {
		Log::error('Blade employee_attendance/import: general exception while generating URL: ' . $e->getMessage());
		$importActionUrl = '#';
	} catch (\Throwable $e) {
		Log::error('Blade employee_attendance/import: throwable while generating URL: ' . $e->getMessage());
		$importActionUrl = '#';
	}
	try {
		$base = Storage::url('uploads/sample');
		$sampleUrl = $base ? asset($base) . '/sample_attendance.csv' : '#';
	} catch (\Error $e) {
		Log::error('Blade employee_attendance/import: sample URL generation error (Error): ' . $e->getMessage());
		$sampleUrl = '#';
	} catch (InvalidArgumentException $e) {
		Log::error('Blade employee_attendance/import: invalid argument while building sample URL: ' . $e->getMessage());
		$sampleUrl = '#';
	} catch (\Exception $e) {
		Log::error('Blade employee_attendance/import: general exception while building sample URL: ' . $e->getMessage());
		$sampleUrl = '#';
	} catch (\Throwable $e) {
		Log::error('Blade employee_attendance/import: throwable while building sample URL: ' . $e->getMessage());
		$sampleUrl = '#';
	}
@endphp
{{ Form::open([
	'url'                  => $importActionUrl,
	'method'               => 'post',
	'enctype'              => 'multipart/form-data',
	'id'                   => $formId,
	'data-resolved-action' => $importActionUrl,
	'data-guard-msg'       => $importGuardMsg,
	'data-sv-localized'    => 'true',
]) }}
	<div class="modal-body">
		<div class="{{ VC::RW }}">
			<div class="{{ VC::C12 }} mb-6">
				{{ Form::label('file', $downloadSampleLabel, ['class' => VC::FM_LB]) }}
				<a id="{{ $sampleLinkId }}" href="{{ $sampleUrl }}" class="{{ VC::BT_SM_PM }}" data-url="{{ $sampleUrl }}" data-guard-msg="{{ base64_encode($sampleGuardMsg) }}" data-sv-localized="true">
					<i class="{{ VC::TI_DWN }}"></i> {{ $downloadLabel }}
				</a>
			</div>
			<div class="{{ VC::C12 }}">
				{{ Form::label('file', $selectCsvLabel, ['class' => VC::FM_LB]) }}
				<div class="choose-file {{ VC::FM_G }}">
					<label for="file" class="{{ VC::FM_LB }}">
						<input type="file" class="{{ VC::FM_CT }}" name="file" id="file" data-filename="upload_file" required>
					</label>
					<p class="upload_file"></p>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<input type="button" value="{{ $cancelLabel }}" class="{{ VC::BT_LG }}" data-bs-dismiss="modal">
		<input type="submit" value="{{ $uploadLabel }}" class="{{ VC::BT_PRM }}">
	</div>
	<script defer src="{{ asset('assets/js/routes/attendances/import.js') }}"></script>
{{ Form::close() }}
