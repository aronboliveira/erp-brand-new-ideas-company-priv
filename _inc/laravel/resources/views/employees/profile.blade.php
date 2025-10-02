@php
	use App\Config\Constants\{ExtendingLayoutsConstants, StacksConstants as ST, ViewClassNamesConstants as VC, ViewsConstants as VW, YieldingConstants};
	use App\Models\Utility;
	use Collective\Html\FormFacade as Form;
	use Illuminate\Support\Facades\{Auth, Crypt, Log, Route, Storage};
	use Illuminate\Support\{Collection, Str};
	use InvalidArgumentException;
	use RuntimeException;
	use TypeError;
	$user ??= null;
	$employees ??= [];
	$branches ??= [];
	$departments ??= [];
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
	$lang = Utility::fetchUserLang(user: $user);
@endphp
@extends(ExtendingLayoutsConstants::ADM)
@section(YieldingConstants::ADM_PG_TTL)
	{{ __('Employee Profile') }}
@endsection
@section(YieldingConstants::ADM_ACT_BTN)
	<div class="{{ VC::RW }} {{ VC::DFL }} {{ VC::JCE }}">
		@php
				$empProfileBase         = VW::EMP.'.profile';
				$empProfileKebab        = Str::kebab($empProfileBase);
				$empProfileResolved     = Route::has($empProfileBase) ? $empProfileBase : (Route::has($empProfileKebab) ? $empProfileKebab : null);
				$empProfileUrl          = $empProfileResolved ? route($empProfileResolved) : '#';
				$empProfileFormId       = 'employee_profile_filter';
				$empProfileGuardMessage = Utility::fetchLinkMessage($lang, VW::EMP, 'profile_employee_route_unavailable') ?? 'Profile employee route is unavailable. Please contact technical support or your domain administrator.';
		@endphp
		{{ Form::open([
				'url'               => $empProfileUrl,
				'method'            => 'GET',
				'enctype'           => 'multipart/form-data',
				'id'                => $empProfileFormId,
				'data-url'          => $empProfileUrl,
				'data-guard-msg'    => $empProfileGuardMessage,
				'data-sv-localized' => 'true',
		]) }}
			<div class="{{ VC::CXL3 }} {{ VC::CL3 }} {{ VC::CM4 }}">
				<div class="all-select-box">
					<div class="btn-box">
						{{ Form::label('branch', __('Branch'), ['class'=>'text-type']) }}
						{{ Form::select('branch', Utility::isFilled($branches) ? $branches : [__('No branches available')], isset($_GET['branch']) ? (string)$_GET['branch'] : '', ['class'=>'select-box select2']) }}
					</div>
				</div>
			</div>
			<div class="{{ VC::CXL3 }} {{ VC::CL3 }} {{ VC::CM4 }}">
				<div class="all-select-box">
					<div class="btn-box">
						{{ Form::label('department', __('Department'), ['class'=>'text-type']) }}
						{{ Form::select('department', Utility::isFilled($departments) ? $departments : [__('No departments available')], isset($_GET['department']) ? (string)$_GET['department'] : '', ['class'=>'select-box select2']) }}
					</div>
				</div>
			</div>
			<div class="{{ VC::CXL3 }} {{ VC::CL3 }} {{ VC::CM4 }}">
				<div class="all-select-box">
					<div class="btn-box">
						{{ Form::label('designation', __('Designation'), ['class'=>'text-type']) }}
						<select class="select2 select-box select2-multiple" id="designation_id" name="designation" data-placeholder="{{ __('Select Designation ...') }}">
							<option value="">{{ __('Designation') }}</option>
						</select>
					</div>
				</div>
			</div>
			<div class="col-auto text-end my-auto">
				<a href="#" class="apply-btn" onclick="document.getElementById('employee_profile_filter').submit(); return false;" data-bs-toggle="tooltip" title="{{ __('Apply') }}">
					<span class="btn-inner--icon"><i class="{{ VC::TI_SRC }}"></i></span>
				</a>
				<a href="{{ route(VW::EMP.'.profile') }}" class="reset-btn" data-bs-toggle="tooltip" title="{{ __('Reset') }}">
					<span class="btn-inner--icon"><i class="ti ti-trash-restore-alt"></i></span>
				</a>
			</div>
			@push(ST::ADM_SCR_PG)
				<script defer src="{{ asset('assets/js/routes/employees/profile.js') }}"></script>
			@endpush
		{{ Form::close() }}
	</div>
@endsection
@section(YieldingConstants::ADM_CTT)
	<div class="{{ VC::RW }}">
		@forelse($employees as $employee)
			<div class="{{ VC::CL3 }} {{ VC::CS6 }} {{ VC::CM6 }}">
				<div class="{{ VC::CD }} profile-card">
					<div class="avatar-parent-child">
						<img src="{{ ($v=data_get($employee,'user.avatar')) ? asset(Storage::url('uploads/avatar')).'/'.$v : asset(Storage::url('uploads/avatar')).'/avatar.png' }}" class="{{ VC::AV_CC }} avatar-xl">
					</div>
					<h4 class="h4 {{ VC::MB0 }} mt-2">{{ isset($employee->name) && $employee->name !== '' ? $employee->name : __('No name available') }}</h4>
					<div class="sal-right-card">
						<span class="{{ VC::BDG }} badge-pill badge-blue">
							{{ data_get($employee,'designation.name') ?: __('No designation available') }}
						</span>
						<div class="Id">
							@can('Show Employee Profile')
								@php
										$employeeIdStr           = (string) data_get($employee ?? null, 'id', '');
										$encryptedEmployeeId     = $employeeIdStr !== '' ? Crypt::encrypt($employeeIdStr) : null;
										$empShowProfileBase      = VW::EMP.'.show.profile';
										$empShowProfileKebab     = Str::kebab($empShowProfileBase);
										$empShowProfileResolved  = Route::has($empShowProfileBase) ? $empShowProfileBase : (Route::has($empShowProfileKebab) ? $empShowProfileKebab : null);
										$empShowProfileUrl       = ($empShowProfileResolved && $encryptedEmployeeId) ? route($empShowProfileResolved, $encryptedEmployeeId) : '#';
										$empShowProfileGuardMsg  = Utility::fetchLinkMessage($lang, VW::EMP, 'show_profile_employee_route_unavailable') ?? 'Show employee profile route is unavailable. Please contact technical support or your domain administrator.';
										$empShowProfileLinkId    = 'employee-show-profile-link-'.($employeeIdStr !== '' ? $employeeIdStr : 'x');
										$hasEmpId                = isset($employee->employee_id) && $employee->employee_id !== '';
										$displayEmpId            = $hasEmpId ? (string) ($user?->employeeIdFormat($employee->employee_id) ?? $employee->employee_id) : __('No employee ID available');
								@endphp
								<a
										id="{{ $empShowProfileLinkId }}"
										href="{{ $empShowProfileUrl }}"
										data-url="{{ $empShowProfileUrl }}"
										data-guard-msg="{{ $empShowProfileGuardMsg }}"
										data-sv-localized="true"
										{{ $empShowProfileUrl === '#' ? 'aria-disabled=true' : '' }}
								>
										{{ $displayEmpId }}
								</a>
								@push(ST::ADM_SCR_PG)
										<script defer src="{{ asset('assets/js/routes/employees/showProfile.js') }}"></script>
								@endpush
							@else
								<a href="#">
									{{ method_exists($employee, 'employeeIdFormat') && isset($employee->employee_id) && $employee->employee_id !== '' ? (string)($user?->employeeIdFormat($employee->employee_id) ?? $employee->employee_id) : __('No employee ID available') }}
								</a>
							@endcan
						</div>
					</div>
				</div>
			</div>
		@empty
			<div class="{{ VC::C12 }}">
				<div class="text-center">
					<h6>{{ __('No employees available') }}</h6>
				</div>
			</div>
		@endforelse
	</div>
@endsection
@push(ST::ADM_SCR_PG)
    <script async src="{{ asset('assets/js/routes/employees/lang/profile.js') }}"></script>
    <script defer>
        (() => {
            const errKey      = 'employee_fetch_unavailable';
            const toastBoxId  = 'toast-box';
            const csrfToken   = '{{ csrf_token() }}';
            const routeUrl    = '{{ route(VW::EMP.".json") }}';
            
            const lang = (() => {
                const l = (sessionStorage.getItem('erp-np-lang') || document.documentElement.lang || 'en')
                .toLowerCase()
                .replace(/_/g, '-');
                return l === 'pt-br' ? l : l.slice(0, 2);
            })();
            
            const tr = key =>
                window.translations?.[lang]?.[key] ||
                window.translations.en?.[key] ||
                '# ERROR';
            
            const showToast = msg => {
                const hasBootstrap = Array.from(document.querySelectorAll('link[rel="stylesheet"]'))
                .some(l => /bootstrap/i.test(l.href)) && window.bootstrap?.Toast;
                if (hasBootstrap) {
                let box = document.getElementById(toastBoxId);
                if (!box) {
                    box = document.createElement('div');
                    box.id = toastBoxId;
                    box.setAttribute('aria-live', 'polite');
                    box.setAttribute('aria-atomic', 'true');
                    document.body.appendChild(box);
                }
                const t = document.createElement('div');
                t.className = 'toast';
                t.innerHTML = `<div class="toast-body">${msg}</div>`;
                box.appendChild(t);
                bootstrap.Toast.getOrCreateInstance(t).show();
                } else {
                alert(msg);
                }
            };
            
            let pendingError = '';
            const flushError = () => {
                if (pendingError) {
                showToast(pendingError);
                pendingError = '';
                }
            };
            document.addEventListener('pointerup', flushError);
            new MutationObserver((records, obs) => {
                for (const rec of records) {
                for (const node of rec.removedNodes) {
                    if (node === document.documentElement) {
                    document.removeEventListener('pointerup', flushError);
                    obs.disconnect();
                    }
                }
                }
            }).observe(document.body, { childList: true, subtree: true });
            
            const getDesignation = deptId => {
                try {
                if (!routeUrl || routeUrl === '#') {
                    pendingError = tr(errKey);
                    return;
                }
                $.ajax({
                    url: routeUrl,
                    type: 'POST',
                    data: { department_id: deptId ?? '', _token: csrfToken },
                })
                    .done(data => {
                    const sel = document.getElementById('designation_id');
                    if (!sel) return;
                    sel.innerHTML = '<option value="">{{ __("Select Designation") }}</option>';
                    for (const [k, v] of Object.entries(data || {})) {
                        sel.insertAdjacentHTML('beforeend',
                        `<option value="${k}">${v}</option>`);
                    }
                    })
                    .fail(() => {
                    pendingError = tr(errKey);
                    });
                } catch {
                pendingError = tr(errKey);
                }
            };
            
            document.addEventListener('DOMContentLoaded', () => {
                const dep = document.getElementById('department');
                if (dep) getDesignation(dep.value);
            });
            
            document.addEventListener('change', e => {
                if (e.target.matches('select[name="department"]')) {
                getDesignation(e.target.value);
                }
            });
        })();
    </script>
@endpush

