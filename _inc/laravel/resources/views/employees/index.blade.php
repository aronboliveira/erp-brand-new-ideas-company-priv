@php
	use App\Config\Constants\{ExtendingLayoutsConstants, StacksConstants as ST, ViewClassNamesConstants as VC, ViewsConstants as VW, YieldingConstants};
	use App\Models\{Utility};
	use Illuminate\Support\{Collection, Str};
	use Illuminate\Support\Facades\{Auth, Crypt, Gate, Log, Route};
	use InvalidArgumentException;
	use RuntimeException;
	use TypeError;
	$user ??= null;
	$lang ??= (string)'';
	$employees ??= [];
	try {
		$user = Auth::user();
	} catch (InvalidArgumentException $e) {
		Log::error('auth_user_fetch_failed', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (RuntimeException $e) {
		Log::error('auth_user_runtime_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (TypeError $e) {
		Log::error('auth_user_type_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Error $e) {
		Log::error('auth_user_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Exception $e) {
		Log::error('auth_user_exception', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Throwable $e) {
		Log::error('auth_user_throwable', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	}
	try {
		$lang = (string)(Utility::fetchUserLang(user:$user) ?? '');
	} catch (InvalidArgumentException $e) {
		Log::error('fetch_user_lang_invalid_argument', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (RuntimeException $e) {
		Log::error('fetch_user_lang_runtime_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (TypeError $e) {
		Log::error('fetch_user_lang_type_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Error $e) {
		Log::error('fetch_user_lang_error', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Exception $e) {
		Log::error('fetch_user_lang_exception', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	} catch (\Throwable $e) {
		Log::error('fetch_user_lang_throwable', ['file'=>$e->getFile(),'line'=>$e->getLine(),'code'=>$e->getCode(),'error_class'=>get_class($e),'message'=>$e->getMessage()]);
	}
	$lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Manage Employee') }}
@endsection
@section(YieldingConstants::ADM_BDC)
	<li class="breadcrumb-item">
		<a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}"{{ Route::has('dashboard') ? '' : ' aria-disabled="true"' }}>
			{{ __('Dashboard') }}
		</a>
	</li>
	<li class="breadcrumb-item">{{ __('Employee') }}</li>
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
    @php
        $empImportBase   = VW::EMP.'.file.import';
        $empImportKebab  = Str::kebab($empImportBase);
        $empImportName   = Route::has($empImportBase) ? $empImportBase : (Route::has($empImportKebab) ? $empImportKebab : null);
        $empImportUrl    = $empImportName ? route($empImportName) : '#';
        $empImportMsg    = Utility::fetchLinkMessage($lang, VW::EMP, 'import_employee_file_route_unavailable') ?? 'Import employee file route is unavailable. Please contact technical support or your domain administrator.';

        $empExportBase   = VW::EMP.'.export';
        $empExportKebab  = Str::kebab($empExportBase);
        $empExportName   = Route::has($empExportBase) ? $empExportBase : (Route::has($empExportKebab) ? $empExportKebab : null);
        $empExportUrl    = $empExportName ? route($empExportName) : '#';
        $empExportMsg    = Utility::fetchLinkMessage($lang, VW::EMP, 'export_employee_route_unavailable') ?? 'Export employee route is unavailable. Please contact technical support or your domain administrator.';

        $empCreateBase   = VW::EMP.'.create';
        $empCreateKebab  = Str::kebab($empCreateBase);
        $empCreateName   = Route::has($empCreateBase) ? $empCreateBase : (Route::has($empCreateKebab) ? $empCreateKebab : null);
        $empCreateUrl    = $empCreateName ? route($empCreateName) : '#';
        $empCreateMsg    = Utility::fetchLinkMessage($lang, VW::EMP, 'create_employee_route_unavailable') ?? 'Create employee route is unavailable. Please contact technical support or your domain administrator.';
    @endphp
    <div class="{{ VC::FEND }}">
        <a
            id="employee-import-btn"
            href="{{ $empImportUrl }}"
            data-url="{{ $empImportUrl }}"
            data-guard-msg="{{ $empImportMsg }}"
            data-sv-localized="true"
            data-size="md"
            data-bs-toggle="tooltip"
            title="{{ __('Import') }}"
            data-ajax-popup="true"
            data-title="{{ __('Import employee CSV file') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="{{ VC::TI_IMP }}"></i>
        </a>
        <a
            id="employee-export-btn"
            href="{{ $empExportUrl }}"
            data-url="{{ $empExportUrl }}"
            data-guard-msg="{{ $empExportMsg }}"
            data-sv-localized="true"
            data-bs-toggle="tooltip"
            title="{{ __('Export') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="{{ VC::TI_EXP }}"></i>
        </a>
        <a
            id="employee-create-btn"
            href="{{ $empCreateUrl }}"
            data-url="{{ $empCreateUrl }}"
            data-guard-msg="{{ $empCreateMsg }}"
            data-sv-localized="true"
            data-bs-toggle="tooltip"
            title="{{ __('Create') }}"
            data-title="{{ __('Create New Employee') }}"
            class="{{ VC::BT_SM_PM }}"
        >
            <i class="{{ VC::TI_PLS }}"></i>
        </a>
    </div>
@endsection
@push(ST::ADM_SCR_PG)
    <script defer src="{{ asset('assets/js/routes/employees/fileImport.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/employees/export.js') }}"></script>
    <script defer src="{{ asset('assets/js/routes/employees/create.js') }}"></script>
@endpush
@section(YieldingConstants::ADM_CTT)
	<div class="{{ VC::RW }}">
		<div class="col-xl-12">
			<div class="{{ VC::CD }}">
				<div class="card-body table-border-style">
					<div class="table-responsive">
						<table class="{{ VC::TB }} datatable">
							<thead>
								<tr>
									<th>{{ __('Employee ID') }}</th>
									<th>{{ __('Name') }}</th>
									<th>{{ __('Email') }}</th>
									<th>{{ __('Branch') }}</th>
									<th>{{ __('Department') }}</th>
									<th>{{ __('Designation') }}</th>
									<th>{{ __('Date Of Joining') }}</th>
									<th>{{ __('Last Login') }}</th>
									<th width="200px">{{ __('Action') }}</th>
								</tr>
							</thead>
							<tbody>
								@php
									$list = (is_array($employees ?? null) || ($employees ?? null) instanceof Collection) ? $employees : [];
									$hasItems = (is_array($list) && count($list) > 0) || ($list instanceof Collection && $list->isNotEmpty());
								@endphp
								@forelse($list as $employee)
									<tr>
										<td class="Id">
											@can('show employee profile')
												@php
														$employeeIdStr     = (string) data_get($employee ?? null, 'id', '');
														$encryptedId       = $employeeIdStr !== '' ? Crypt::encrypt($employeeIdStr) : null;
														$empShowBase       = VW::EMP.'.show';
														$empShowKebab      = Str::kebab($empShowBase);
														$empShowResolved   = Route::has($empShowBase) ? $empShowBase : (Route::has($empShowKebab) ? $empShowKebab : null);
														$empShowUrl        = ($empShowResolved && $encryptedId) ? route($empShowResolved, $encryptedId) : '#';
														$empShowGuardMsg   = Utility::fetchLinkMessage($lang, VW::EMP, 'show_employee_route_unavailable') ?? 'Show employee route is unavailable. Please contact technical support or your domain administrator.';
														$showLinkId        = 'employee-show-btn-'.($employeeIdStr !== '' ? $employeeIdStr : 'x');
														$hasEmpCode        = isset($employee->employee_id) && $employee->employee_id !== '';
														$displayEmpCode    = $hasEmpCode ? (string) ($user?->employeeIdFormat($employee->employee_id) ?? $employee->employee_id) : __('No employee ID available');
												@endphp
												<a
														id="{{ $showLinkId }}"
														href="{{ $empShowUrl }}"
														data-url="{{ $empShowUrl }}"
														data-guard-msg="{{ $empShowGuardMsg }}"
														data-sv-localized="true"
														class="{{ VC::BT_OUTPM }}"
														{{ $empShowUrl === '#' ? 'aria-disabled=true' : '' }}
												>
														{{ $displayEmpCode }}
												</a>
												@push(ST::ADM_SCR_PG)
														<script defer src="{{ asset('assets/js/routes/employees/show.js') }}"></script>
												@endpush
											@else
												<a href="#" class="{{ VC::BT_OUTPM }}">
													{{ isset($employee->employee_id) && $employee->employee_id !== '' ? (string)($user?->employeeIdFormat($employee->employee_id) ?? $employee->employee_id) : __('No employee ID available') }}
												</a>
											@endcan
										</td>
										<td class="font-style">{{ isset($employee->name) && $employee->name !== '' ? $employee->name : __('No name available') }}</td>
										<td>{{ isset($employee->email) && $employee->email !== '' ? $employee->email : __('No email available') }}</td>
										<td class="font-style">
											{{ isset($employee->branch_id) && $employee->branch_id ? (data_get($user?->getBranch($employee->branch_id),'name') ?: __('No branch name available')) : __('Failed to get branch name') }}
										</td>
										<td class="font-style">
											{{ isset($employee->department_id) && $employee->department_id ? (data_get($user?->getDepartment($employee->department_id),'name') ?: __('No department name available')) : __('Failed to get department name') }}
										</td>
										<td class="font-style">
											{{ isset($employee->designation_id) && $employee->designation_id ? (data_get($user?->getDesignation($employee->designation_id),'name') ?: __('No designation name available')) : __('Failed to get designation name') }}
										</td>
										<td class="font-style">
											{{ !empty($employee->company_doj) ? (string)($user?->dateFormat($employee->company_doj) ?? '-') : '-' }}
										</td>
										<td>{{ data_get($employee,'user.last_login_at') ?: '-' }}</td>
										@php $canEdit = Gate::check('edit employee'); $canDelete = Gate::check('delete employee'); @endphp
										@if($canEdit || $canDelete)
											<td>
												@if(isset($employee->is_active) && (int)$employee->is_active === 1)
													@can('edit employee')
														<div class="{{ VC::ACT_BTN_PRIM }}">
														@php
																$employeeIdStr    = (string) data_get($employee ?? null, 'id', '');
																$encryptedId      = $employeeIdStr !== '' ? Crypt::encrypt($employeeIdStr) : null;
																$empEditBase      = VW::EMP.'.edit';
																$empEditKebab     = Str::kebab($empEditBase);
																$empEditResolved  = Route::has($empEditBase) ? $empEditBase : (Route::has($empEditKebab) ? $empEditKebab : null);
																$empEditUrl       = ($empEditResolved && $encryptedId) ? route($empEditResolved, $encryptedId) : '#';
																$empEditGuardMsg  = Utility::fetchLinkMessage($lang, VW::EMP, 'edit_employee_route_unavailable') ?? 'Edit employee route is unavailable. Please contact technical support or your domain administrator.';
																$editLinkId       = 'employee-edit-btn-'.($employeeIdStr !== '' ? $employeeIdStr : 'x');
														@endphp
														<a
																id="{{ $editLinkId }}"
																href="{{ $empEditUrl }}"
																data-url="{{ $empEditUrl }}"
																data-guard-msg="{{ $empEditGuardMsg }}"
																data-sv-localized="true"
																class="{{ VC::BT_SM_CT }}"
																data-bs-toggle="tooltip"
																title="{{ __('Edit') }}"
																{{ $empEditUrl === '#' ? 'aria-disabled=true' : '' }}
														>
																<i class="{{ VC::TI_PC_WT }}"></i>
														</a>
														@push(ST::ADM_SCR_PG)
																<script defer src="{{ asset('assets/js/routes/employees/edit.js') }}"></script>
														@endpush
														</div>
													@endcan
													@can('delete employee')
														<div class="{{ VC::ACT_BTN_DNG_2 }}">
															@php
																	$employeeIdStr     = (string) data_get($employee ?? null, 'id', '');
																	$empDestroyBase    = VW::EMP.'.destroy';
																	$empDestroyKebab   = Str::kebab($empDestroyBase);
																	$empDestroyName    = Route::has($empDestroyBase) ? $empDestroyBase : (Route::has($empDestroyKebab) ? $empDestroyKebab : null);
																	$empDestroyUrl     = ($empDestroyName && $employeeIdStr !== '') ? route($empDestroyName, [$employeeIdStr]) : '#';
																	$destroyFormId     = 'delete-form-'.($employeeIdStr !== '' ? $employeeIdStr : 'x');
																	$destroyBtnId      = 'delete-employee-btn-'.($employeeIdStr !== '' ? $employeeIdStr : 'x');
																	$destroyGuardMsg   = Utility::fetchLinkMessage($lang, VW::EMP, 'destroy_employee_route_unavailable') ?? 'Delete employee route is unavailable. Please contact technical support or your domain administrator.';
																	$areYouSure        = Utility::fetchLinkMessage($lang, 'generics', 'are_you_sure') ?? 'Are You Sure?';
																	$irreversible      = Utility::fetchLinkMessage($lang, 'generics', 'irreversible_action') ?? 'This action can not be undone. Do you want to continue?';
															@endphp
															{{ Form::open([
																	'method' => 'DELETE',
																	'url'    => $empDestroyUrl,
																	'id'     => $destroyFormId,
																	'data-sv-localized' => 'true',
															]) }}
																	<a
																			id="{{ $destroyBtnId }}"
																			href="{{ $empDestroyUrl }}"
																			class="{{ VC::BT_SM_CT_PR }}"
																			data-bs-toggle="tooltip"
																			title="{{ __('Delete') }}"
																			data-confirm="{{ __($areYouSure) }}|{{ __($irreversible) }}"
																			data-confirm-yes="document.getElementById('{{ $destroyFormId }}').submit();"
																			data-url="{{ $empDestroyUrl }}"
																			data-guard-msg="{{ $destroyGuardMsg }}"
																			data-sv-localized="true"
																			{{ $empDestroyUrl === '#' ? 'aria-disabled=true' : '' }}
																	>
																			<i class="{{ VC::TI_TRS_WT }}"></i>
																	</a>
															{{ Form::close() }}
															@push(ST::ADM_SCR_PG)
																	<script defer src="{{ asset('assets/js/routes/employees/destroy.js') }}"></script>
															@endpush
														</div>
													@endcan
												@else
													<i class="ti ti-lock"></i>
												@endif
											</td>
										@endif
									</tr>
								@empty
									<tr>
										<td colspan="9">
											<div class="text-center">{{ __('No employees available') }}</div>
										</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
